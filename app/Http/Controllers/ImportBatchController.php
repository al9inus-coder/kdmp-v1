<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\FiscalYear;
use App\Models\ImportBatch;
use App\Models\ImportBatchError;
use App\Models\Package;
use App\Models\SubActivity;
use App\Services\XlsxReader;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Support\Facades\Gate;
use Throwable;

class ImportBatchController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', ImportBatch::class);

        $batches = ImportBatch::query()
            ->with(['fiscalYear', 'creator'])
            ->whereIn('status', [
                'completed',
                'completed_with_errors'
            ])
            ->orderByDesc('id')
            ->paginate(5);
        $fiscalYears = FiscalYear::query()
            ->orderBy('tahun', 'desc')
            ->get();
        $activeFiscalYearId = FiscalYear::query()
            ->where('is_active', true)
            ->value('id');

        return view('packages.import', compact(
            'batches',
            'fiscalYears',
            'activeFiscalYearId'
        ));
    }

    public function store(Request $request, XlsxReader $xlsxReader): RedirectResponse
    {
        Gate::authorize('create', ImportBatch::class);
       
        $isStaf = $request->input('source') === 'staf';

        $validated = $request->validate([
            'file'           => ['required', 'file', 'mimes:xlsx', 'max:10240'],
            'fiscal_year_id' => [$isStaf ? 'nullable' : 'required', 'exists:fiscal_years,id'],
        ], [
            'file.mimes' => 'File harus berformat .xlsx',
            'file.max'   => 'Ukuran file maksimal 10MB.',
        ]);

        // Jika dari staf dan fiscal_year_id kosong, ambil tahun aktif otomatis
        if ($isStaf && empty($validated['fiscal_year_id'])) {
            $activeFiscalYear = \App\Models\FiscalYear::where('is_active', true)->first()
                ?? \App\Models\FiscalYear::orderBy('tahun', 'desc')->first();

            if (!$activeFiscalYear) {
                return $this->redirectBack($isStaf)->with('error', 'Tidak ada Tahun Anggaran aktif. Hubungi Admin.');
            }

            $validated['fiscal_year_id'] = $activeFiscalYear->id;
        }

        $uploadedFile = $validated['file'];
        
        $filename = uniqid().'_'.$uploadedFile->getClientOriginalName();

        $uploadedFile->move(
            storage_path('app/private/imports/rup'),
            $filename
        );

        $storedPath = 'imports/rup/'.$filename;

        $batch = ImportBatch::create([
            'fiscal_year_id' => $validated['fiscal_year_id'],
            'created_by' => Auth::id(),
            'file_name' => $uploadedFile->getClientOriginalName(),
            'file_path' => $storedPath,
            'status' => 'processing',
            'total_rows' => 0,
            'success_rows' => 0,
            'failed_rows' => 0,
        ]);
        try {

            $fullPath = Storage::disk('local')->path($storedPath);

            $rows = $xlsxReader->readRows($fullPath);

        } catch (Throwable $e) {

            $batch->update([
                'status' => 'failed',
                'notes' => $e->getMessage(),
            ]);

            return $this->redirectBack($isStaf)->with('error', 'Gagal membaca file Excel. '.$e->getMessage());
        }

        if ($rows === []) {
            $batch->update([
                'status' => 'failed',
                'notes' => 'File Excel kosong.',
            ]);

            return $this->redirectBack($isStaf)->with('error', 'File Excel kosong.');
        }

        $headerMap = $this->buildHeaderMap($rows[0]);

        if (!$this->hasRequiredHeader($headerMap)) {
            $batch->update([
                'status' => 'failed',
                'notes' => 'Header Excel tidak sesuai template import RUP.',
            ]);

            return $this->redirectBack($isStaf)->with('error', 'Header Excel tidak sesuai template import RUP.');
        }

        $subActivityMap = $this->buildSubActivityMap();
        $accountMap = $this->buildCodeMap(Account::query()->pluck('id', 'kode')->all());

        $totalRows = 0;
        $successRows = 0;
        $failedRows = 0;

        DB::beginTransaction();

        try {
            foreach (array_slice($rows, 1) as $index => $row) {
                $rowNumber = $index + 2; // Assuming the first row is the header
                if ($this->isRowEmpty($row)) {
                    continue;
                }

                $totalRows++;

                $namaPaket = trim((string) ($this->value($row, $headerMap, 'nama_paket') ?? ''));

                $subActivityCode = $this->value($row, $headerMap, 'kode_sub_kegiatan');
                $accountCode = $this->value($row, $headerMap, 'kode_rekening');

                $subActivityData = $this->resolveSubActivityData(
                    $subActivityMap,
                    $subActivityCode
                );

                $accountId = $this->resolveIdFromMap(
                    $accountMap,
                    $accountCode
                );

                $status = ($subActivityData['sub_activity_id'] && $accountId)
                    ? 'draft'
                    : 'needs_review';

                if (
                    empty($namaPaket) ||
                    !$subActivityData['sub_activity_id'] ||
                    !$accountId
                ) {
                    $status = 'needs_review';
                }

                $idRup = trim((string) ($this->value($row, $headerMap, 'id_rup') ?? ''));

                /*
                |--------------------------------------------------------------------------
                | ID RUP wajib ada
                |--------------------------------------------------------------------------
                */
                if ($idRup === '') {

                    ImportBatchError::create([
                        'import_batch_id' => $batch->id,
                        'row_number'      => $rowNumber,
                        'id_rup'          => null,
                        'error_type'      => 'missing_id_rup',
                        'error_message'   => 'ID RUP kosong.',
                    ]);

                    $failedRows++;
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | ID RUP tidak boleh duplikat
                |--------------------------------------------------------------------------
                */
                if (Package::where('id_rup', $idRup)->exists()) {

                    ImportBatchError::create([
                        'import_batch_id' => $batch->id,
                        'row_number'      => $rowNumber,
                        'id_rup'          => $idRup,
                        'error_type'      => 'duplicate_id_rup',
                        'error_message'   => 'ID RUP sudah terdaftar.',
                    ]);

                    $failedRows++;
                    continue;
                }

                try {
                    Package::create([
                        'import_batch_id' => $batch->id,
                        'fiscal_year_id'  => $batch->fiscal_year_id,

                        'program_id'      => $subActivityData['program_id'],
                        'activity_id'     => $subActivityData['activity_id'],
                        'sub_activity_id' => $subActivityData['sub_activity_id'],
                        'account_id'      => $accountId,

                        'id_rup'          => $idRup,
                        'nama_paket'      => $namaPaket,

                        'pagu'            => $this->parsePagu(
                            $this->value($row, $headerMap, 'pagu')
                        ),

                        'jenis_pengadaan'  => $this->value($row, $headerMap, 'jenis_pengadaan'),
                        'metode_pengadaan' => $this->value($row, $headerMap, 'metode_pengadaan'),

                        'pemilihan_mulai_bulan'   => $this->parseMonth($this->value($row, $headerMap, 'pemilihan_mulai')),
                        'pemilihan_selesai_bulan' => $this->parseMonth($this->value($row, $headerMap, 'pemilihan_selesai')),
                        'kontrak_mulai_bulan'     => $this->parseMonth($this->value($row, $headerMap, 'kontrak_mulai')),
                        'kontrak_selesai_bulan'   => $this->parseMonth($this->value($row, $headerMap, 'kontrak_selesai')),

                        'status' => $status,
                    ]);

                    $successRows++;

                } catch (Throwable $e) {

                    ImportBatchError::create([
                        'import_batch_id' => $batch->id,
                        'row_number'      => $rowNumber,
                        'id_rup'          => $idRup,
                        'error_type'      => 'system_error',
                        'error_message'   => $e->getMessage(),
                    ]);

                    $failedRows++;
                }
            }

            $batch->update([
                'total_rows' => $totalRows,
                'success_rows' => $successRows,
                'failed_rows' => $failedRows,
                'status' => $failedRows > 0 ? 'completed_with_errors' : 'completed',
                'imported_at' => now(),
            ]);

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();

            $batch->update([
                'status' => 'failed',
                'notes' => $e->getMessage(),
            ]);

            return $this->redirectBack($isStaf)->with('error', 'Import gagal diproses. '.$e->getMessage());
        }

        return $this->redirectBack($isStaf)
            ->with('success', 'Import RUP selesai. Berhasil: '.$successRows.', gagal: '.$failedRows.'.');
    }

    private function redirectBack(bool $isStaf): \Illuminate\Http\RedirectResponse
    {
        if ($isStaf) {
            return redirect()->route('staf.packages.import');
        }
        return redirect()->route('admin.packages.import.index');
    }

    /**
     * @param array<int, string|null> $headerRow
     * @return array<string, int>
     */
    private function buildHeaderMap(array $headerRow): array
    {
        $aliases = [
            'id_rup' => [
                'id rup', 'id_rup', 'idrup',
                'no rup', 'no_rup', 'no. rup', 'nomor rup',
            ],
            'nama_paket' => [
                'nama paket', 'nama_paket',
            ],
            'kode_sub_kegiatan' => [
                'kode sub kegiatan', 'kode_sub_kegiatan', 'kode subkegiatan',
                'sub kegiatan', 'subkegiatan',
            ],
            'kode_rekening' => [
                'kode rekening', 'kode_rekening', 'kode rekening belanja',
                'mak', 'kode mak', 'rekening',
                // template baru: kolom index 5 berisi kode MAK/rekening
                'kode mak / rekening', 'kode rekening belanja (mak)',
            ],
            'pagu' => [
                'pagu', 'nilai pagu',
            ],
            'jenis_pengadaan' => [
                'jenis pengadaan',
            ],
            'metode_pengadaan' => [
                'metode pengadaan',
                'metode pemilihan',
            ],
            'pemilihan_mulai' => [
                'pemilihan mulai',
                'waktu mulai pemilihan', 'waktu awal pemilihan',
                'bulan mulai pemilihan',
            ],
            'pemilihan_selesai' => [
                'pemilihan selesai',
                'waktu akhir pemilihan',
                'bulan akhir pemilihan',
            ],
            'kontrak_mulai' => [
                'kontrak mulai',
                'waktu awal kontrak', 'waktu mulai kontrak',
                'bulan awal kontrak', 'bulan mulai kontrak',
            ],
            'kontrak_selesai' => [
                'kontrak selesai',
                'waktu akhir kontrak',
                'aaktu akhir kontrak', // typo di template lama
                'bulan akhir kontrak',
            ],
        ];

        $map = [];

        foreach ($headerRow as $index => $value) {
            $normalized = $this->normalizeText((string) $value);

            if ($normalized === '') {
                continue;
            }

            foreach ($aliases as $key => $aliasList) {
                foreach ($aliasList as $alias) {
                    if ($normalized === $this->normalizeText($alias)) {
                        $map[$key] = $index;
                        break 2;
                    }
                }
            }
        }

        return $map;
    }

    /**
     * @param array<string, int> $headerMap
     */
    private function hasRequiredHeader(array $headerMap): bool
    {
        $required = [
            'id_rup',
            'nama_paket',
            'kode_sub_kegiatan',
            'pagu',
            'jenis_pengadaan',
            'metode_pengadaan',
            'pemilihan_mulai',
            'pemilihan_selesai',
            'kontrak_mulai',
            'kontrak_selesai',
        ];

        foreach ($required as $column) {
            if (!array_key_exists($column, $headerMap)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<int, string|null> $row
     * @param array<string, int> $headerMap
     */
    private function value(array $row, array $headerMap, string $column): ?string
    {
        if (!isset($headerMap[$column])) {
            return null;
        }

        $value = $row[$headerMap[$column]] ?? null;

        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);
        return $trimmed === '' ? null : $trimmed;
    }

    /**
     * @param array<int, string|null> $row
     */
    private function isRowEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string, int> $source
     * @return array<string, int>
     */
    private function buildCodeMap(array $source): array
    {
        $map = [];

        foreach ($source as $code => $id) {
            $normalized = $this->normalizeText((string) $code);

            if ($normalized !== '') {
                $map[$normalized] = $id;
            }
        }

        return $map;
    }

    /**
     * @param array<string, int> $map
     */
    private function resolveIdFromMap(array $map, ?string $name): ?int
    {
        if ($name === null) {
            return null;
        }

        $normalized = $this->normalizeText($name);

        if ($normalized === '') {
            return null;
        }

        return $map[$normalized] ?? null;
    }

    private function normalizeText(string $value): string
    {
        // Hapus tanda kutip di awal/akhir (jika Excel menyimpan header dengan kutip)
        $value = trim($value, " \t\n\r\0\x0B\"'");
        $value = preg_replace('/\s+/', ' ', trim(mb_strtolower($value))) ?? '';
        return $value;
    }

    private function parsePagu(?string $value): float
    {
        if ($value === null || trim($value) === '') {
            return 0;
        }

        $clean = str_replace(['Rp', 'rp', ' '], '', $value);
        $clean = str_replace('.', '', $clean);
        $clean = str_replace(',', '.', $clean);

        return is_numeric($clean) ? (float) $clean : 0;
    }

    private function parseMonth(?string $value): ?int
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $value = trim($value);

        if (is_numeric($value)) {
            $number = (float) $value;

            if ($number >= 1 && $number <= 12) {
                return (int) $number;
            }

            if ($number > 12) {
                $date = Carbon::createFromDate(1899, 12, 30)->addDays((int) $number);
                return (int) $date->month;
            }
        }

        $monthMap = [
            'januari' => 1,
            'january' => 1,
            'februari' => 2,
            'february' => 2,
            'maret' => 3,
            'march' => 3,
            'april' => 4,
            'mei' => 5,
            'may' => 5,
            'juni' => 6,
            'june' => 6,
            'juli' => 7,
            'july' => 7,
            'agustus' => 8,
            'august' => 8,
            'september' => 9,
            'oktober' => 10,
            'october' => 10,
            'november' => 11,
            'desember' => 12,
            'december' => 12,
        ];

        $normalized = $this->normalizeText($value);

        if (isset($monthMap[$normalized])) {
            return $monthMap[$normalized];
        }

        $timestamp = strtotime($value);

        if ($timestamp === false) {
            return null;
        }

        return (int) date('n', $timestamp);
    }

    /**
     * @return array<string, array{program_id: int|null, activity_id: int|null, sub_activity_id: int|null}>
     */
    private function buildSubActivityMap(): array
    {
        $map = [];

        $subActivities = SubActivity::query()
            ->with('activity')
            ->get(['id', 'activity_id', 'kode']);

        foreach ($subActivities as $subActivity) {
            $normalizedCode = $this->normalizeText((string) $subActivity->kode);

            if ($normalizedCode === '') {
                continue;
            }

            $map[$normalizedCode] = [
                'program_id' => $subActivity->activity?->program_id,
                'activity_id' => $subActivity->activity_id,
                'sub_activity_id' => $subActivity->id,
            ];
        }

        return $map;
    }

    /**
     * @param array<string, array{program_id: int|null, activity_id: int|null, sub_activity_id: int|null}> $map
     * @return array{program_id: int|null, activity_id: int|null, sub_activity_id: int|null}
     */
    private function resolveSubActivityData(array $map, ?string $code): array
    {
        if ($code === null) {
            return [
                'program_id' => null,
                'activity_id' => null,
                'sub_activity_id' => null,
            ];
        }

        $normalized = $this->normalizeText($code);

        if ($normalized === '' || !isset($map[$normalized])) {
            return [
                'program_id' => null,
                'activity_id' => null,
                'sub_activity_id' => null,
            ];
        }

        return $map[$normalized];
    }

    public function show(ImportBatch $batch)
    {
        Gate::authorize('view', $batch);

        $batch->load([
            'fiscalYear',
            'creator',
            'errors'
        ]);

        return view(
            'packages.import-detail',
            compact('batch')
        );
    }
}
