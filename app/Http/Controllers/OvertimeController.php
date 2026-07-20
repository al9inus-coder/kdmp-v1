<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Overtime;
use App\Models\OvertimeDetail;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OvertimeController extends Controller
{
    public function index(Package $package)
    {
        // View to show 12 months cards
        return view('overtimes.index', compact('package'));
    }

    public function show(Package $package, $month)
    {
        $year = date('Y'); // fallback if package doesn't have year
        // We can use package created_at year or a specific field if it exists. Let's assume current year or package created_at.
        $year = $package->created_at ? $package->created_at->format('Y') : date('Y');

        $overtime = Overtime::firstOrCreate([
            'package_id' => $package->id,
            'bulan' => $month,
            'tahun' => $year
        ]);

        $mode = $this->resolveMode($package, $overtime);

        // We also need SBU Lembur rates
        $sbuRates = \App\Models\SbuLembur::all();
        
        // Ambil hari libur dari database (tabel holidays)
        $holidaysDataFull = [];
        $dbHolidays = \App\Models\Holiday::whereYear('holiday_date', $year)->get();
        foreach($dbHolidays as $h) {
            $holidaysDataFull[] = [
                'date' => $h->holiday_date,
                'description' => $h->description
            ];
        }
        
        // Pass to view for calendar
        $overtime->load('details.employee');

        return view('overtimes.show_calendar', compact('package', 'overtime', 'month', 'year', 'sbuRates', 'holidaysDataFull', 'mode'));
    }

    /**
     * Mode lembur direkam per paket di kolom jenis_lembur ('dinas' | 'kebersihan').
     * - Data lama (roster sudah ter-seed sebelum fitur mode ada) dianggap 'dinas'.
     * - Bulan lain pada paket yang sama mewarisi mode yang sudah dipilih.
     * - null berarti mode belum dipilih → view menampilkan layar pilihan.
     */
    protected function resolveMode(Package $package, Overtime $overtime): ?string
    {
        if ($overtime->jenis_lembur) {
            if ($overtime->jenis_lembur === 'dinas' && $overtime->details()->count() == 0) {
                $this->seedDinasRoster($overtime);
            }
            return $overtime->jenis_lembur;
        }

        if ($overtime->details()->count() > 0) {
            $overtime->update(['jenis_lembur' => 'dinas']);
            return 'dinas';
        }

        $inherited = Overtime::where('package_id', $package->id)
            ->whereNotNull('jenis_lembur')
            ->value('jenis_lembur');

        if ($inherited) {
            $overtime->update(['jenis_lembur' => $inherited]);
            if ($inherited === 'dinas') {
                $this->seedDinasRoster($overtime);
            }
            return $inherited;
        }

        return null;
    }

    protected function seedDinasRoster(Overtime $overtime): void
    {
        foreach (Employee::where('tipe', Employee::TIPE_DINAS)->get() as $employee) {
            OvertimeDetail::firstOrCreate([
                'overtime_id' => $overtime->id,
                'employee_id' => $employee->id,
            ], [
                'daily_hours' => [],
                'use_uang_makan' => false,
            ]);
        }
    }

    public function chooseMode(Request $request, Package $package, Overtime $overtime)
    {
        $this->authorizeOvertime($package, $overtime);

        $validated = $request->validate([
            'mode' => 'required|in:dinas,kebersihan',
        ]);

        if ($overtime->jenis_lembur || $overtime->details()->count() > 0) {
            return redirect()->back()->with('error', 'Mode lembur paket ini sudah dipilih.');
        }

        $overtime->update(['jenis_lembur' => $validated['mode']]);

        if ($validated['mode'] === 'dinas') {
            $this->seedDinasRoster($overtime);
        }

        return redirect()->back()->with('success', 'Mode lembur berhasil dipilih: ' . ($validated['mode'] === 'dinas' ? 'Pegawai Dinas' : 'Petugas Kebersihan') . '.');
    }

    /**
     * Batalkan pilihan mode untuk SELURUH bulan pada paket ini.
     * Boleh bila belum ada jam terisi; bila sudah ada, hanya Admin (dengan
     * konsekuensi seluruh roster + jam bulan yang belum terkunci terhapus).
     * Bulan terkunci memblokir reset.
     */
    public function resetMode(Request $request, Package $package, Overtime $overtime)
    {
        $this->authorizeOvertime($package, $overtime);

        $overtimes = Overtime::where('package_id', $package->id)->with('details')->get();

        if ($overtimes->contains(fn ($o) => $o->is_locked)) {
            return redirect()->back()->with('error', 'Ada bulan yang sudah dikunci. Buka kuncinya dulu sebelum mengubah mode.');
        }

        $hasHours = $overtimes->contains(function ($o) {
            return $o->details->contains(fn ($d) => !empty(array_filter($d->daily_hours ?? [])));
        });

        $userRole = auth()->user()->getRoleNames()->first() ?? '';
        if ($hasHours && $userRole !== 'Admin') {
            return redirect()->back()->with('error', 'Sudah ada data jam lembur terisi. Hanya Admin yang dapat mengubah mode (seluruh data lembur paket ini akan dihapus).');
        }

        foreach ($overtimes as $o) {
            $o->details()->delete();
            $o->update(['jenis_lembur' => null]);
        }

        return redirect()->back()->with('success', 'Mode lembur direset. Silakan pilih ulang mode untuk paket ini.');
    }

    /**
     * Import kehadiran lembur petugas kebersihan untuk SATU tanggal.
     * File: kolom Nama (wajib) + Jam (opsional; kosong = 2 jam hari kerja / 5 jam akhir pekan-libur).
     * Nama yang belum terdaftar otomatis dibuat sebagai pegawai bertipe kebersihan.
     */
    public function importAttendance(Request $request, Package $package, Overtime $overtime)
    {
        $this->authorizeOvertime($package, $overtime);

        if ($overtime->is_locked) {
            return redirect()->back()->with('error', 'Data lembur bulan ini sudah dikunci.');
        }

        if ($overtime->jenis_lembur !== 'kebersihan') {
            return redirect()->back()->with('error', 'Upload kehadiran hanya untuk lembur Petugas Kebersihan.');
        }

        if ($pesan = $this->editDeniedMessage($overtime)) {
            return redirect()->back()->with('error', $pesan);
        }

        $validated = $request->validate([
            'date' => 'required|date',
            'file' => 'required|file|mimes:xlsx,csv,txt|max:5120',
        ]);

        $date = \Carbon\Carbon::parse($validated['date']);
        if ((int) $date->format('n') !== (int) $overtime->bulan || (int) $date->format('Y') !== (int) $overtime->tahun) {
            return redirect()->back()->with('error', 'Tanggal harus berada dalam bulan lembur ini (' . $overtime->bulan . '/' . $overtime->tahun . ').');
        }

        try {
            $rows = $this->readAttendanceRows($request->file('file'));
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', 'File tidak dapat dibaca: ' . $e->getMessage());
        }

        if ($rows === []) {
            return redirect()->back()->with('error', 'File kosong atau tidak ada baris nama yang terbaca.');
        }

        $day = (int) $date->format('j');

        $pool = Employee::where('tipe', Employee::TIPE_KEBERSIHAN)->get();
        $byNip = $pool->filter(fn ($e) => filled($e->nip))->keyBy(fn ($e) => $this->cleanCell((string) $e->nip));
        $byNama = $pool->keyBy(fn ($e) => mb_strtolower(trim($e->nama)));

        $recorded = 0;
        $created = 0;
        $skippedNama = 0;   // baris tanpa nama
        $skippedJam = 0;    // jam kosong / 0 -> tidak lembur
        $belowMin = 0;      // 0 < jam < 2 -> tidak memenuhi syarat

        \Illuminate\Support\Facades\DB::transaction(function () use (
            $rows, $byNip, $byNama, $overtime, $day,
            &$recorded, &$created, &$skippedNama, &$skippedJam, &$belowMin
        ) {
            // Upload ulang untuk tanggal yang sama bersifat MENGGANTI: kosongkan
            // dulu jam tanggal ini agar orang yang tidak ada di file terbaru
            // tidak tertinggal dari upload sebelumnya.
            foreach ($overtime->details()->get() as $existingDetail) {
                $dh = $existingDetail->daily_hours ?? [];
                if (array_key_exists($day, $dh)) {
                    unset($dh[$day]);
                    $existingDetail->daily_hours = $dh;
                    $existingDetail->save();
                }
            }

            foreach ($rows as $row) {
                $nama = trim((string) ($row['nama'] ?? ''));
                if ($nama === '') {
                    $skippedNama++;
                    continue;
                }

                $jamRaw = trim((string) ($row['jam'] ?? ''));
                if ($jamRaw === '' || !is_numeric($jamRaw)) {
                    // Terima bersih: jam kosong berarti tidak lembur (mis. TK).
                    $skippedJam++;
                    continue;
                }

                $hours = (int) round((float) $jamRaw);
                if ($hours <= 0) {
                    $skippedJam++;
                    continue;
                }
                if ($hours < 2) {
                    // Aturan lembur: minimal 2 jam dalam 1 hari.
                    $belowMin++;
                    continue;
                }

                $nip = $this->cleanCell((string) ($row['nip'] ?? ''));
                // NIP yang terlanjur berubah jadi notasi ilmiah oleh Excel
                // (mis. 1.99E+17) sudah kehilangan digit — jangan dipakai
                // mencocokkan/menyimpan, cukup jatuh ke pencocokan nama.
                if ($nip !== '' && preg_match('/[eE][+-]?\d+$/', $nip)) {
                    $nip = '';
                }
                $jabatan = trim((string) ($row['jabatan'] ?? ''));
                $namaKey = mb_strtolower($nama);

                // Cocokkan dengan NIP lebih dulu (lebih akurat), baru nama.
                $employee = ($nip !== '' ? $byNip->get($nip) : null) ?? $byNama->get($namaKey);

                if (!$employee) {
                    $employee = Employee::create([
                        'nama' => $nama,
                        'nip' => $nip !== '' ? $nip : null,
                        'jabatan' => $jabatan !== '' ? $jabatan : null,
                        'tipe' => Employee::TIPE_KEBERSIHAN,
                    ]);
                    $created++;
                } else {
                    // Lengkapi data yang masih kosong dari file.
                    $fill = [];
                    if (blank($employee->nip) && $nip !== '') { $fill['nip'] = $nip; }
                    if (blank($employee->jabatan) && $jabatan !== '') { $fill['jabatan'] = $jabatan; }
                    if ($fill !== []) { $employee->update($fill); }
                }

                if ($nip !== '') { $byNip->put($nip, $employee); }
                $byNama->put($namaKey, $employee);

                $detail = OvertimeDetail::firstOrCreate([
                    'overtime_id' => $overtime->id,
                    'employee_id' => $employee->id,
                ], [
                    'daily_hours' => [],
                    'use_uang_makan' => false,
                ]);

                $dailyHours = $detail->daily_hours ?? [];
                $dailyHours[$day] = $hours;
                $detail->daily_hours = $dailyHours;
                $detail->save();

                $recorded++;
            }
        });

        if ($recorded === 0 && $created === 0) {
            $detailPesan = [];
            if ($skippedJam > 0) { $detailPesan[] = "{$skippedJam} baris tanpa jam"; }
            if ($belowMin > 0) { $detailPesan[] = "{$belowMin} baris < 2 jam"; }
            if ($skippedNama > 0) { $detailPesan[] = "{$skippedNama} baris tanpa nama"; }
            $suffix = $detailPesan ? ' (' . implode(', ', $detailPesan) . ')' : '';

            return redirect()->back()->with('error',
                'Tidak ada kehadiran yang tercatat' . $suffix . '. Pastikan file memiliki kolom "Jam" berisi jam lembur bersih (minimal 2).');
        }

        $message = "Kehadiran tanggal {$date->format('d-m-Y')} tercatat: {$recorded} petugas";
        if ($created > 0) {
            $message .= ", {$created} pegawai baru dibuat";
        }
        if ($belowMin > 0) {
            $message .= ", {$belowMin} tidak dicatat karena < 2 jam";
        }
        if ($skippedJam > 0) {
            $message .= ", {$skippedJam} tanpa jam lembur";
        }
        if ($skippedNama > 0) {
            $message .= ", {$skippedNama} baris tanpa nama";
        }

        return redirect()->back()->with('success', $message . '.');
    }

    /**
     * Tambah kehadiran SATU petugas pada satu tanggal (mode kebersihan) —
     * untuk nama yang terlewat dari file upload, tanpa harus upload ulang.
     * Pencocokan nama & auto-buat pegawai mengikuti perilaku importAttendance.
     */
    public function addAttendance(Request $request, Package $package, Overtime $overtime)
    {
        $this->authorizeOvertime($package, $overtime);

        if ($overtime->is_locked) {
            return response()->json(['success' => false, 'message' => 'Data lembur bulan ini sudah dikunci.'], 422);
        }

        if ($overtime->jenis_lembur !== 'kebersihan') {
            return response()->json(['success' => false, 'message' => 'Penambahan petugas hanya untuk lembur Petugas Kebersihan.'], 422);
        }

        if ($pesan = $this->editDeniedMessage($overtime)) {
            return response()->json(['success' => false, 'message' => $pesan], 403);
        }

        $validated = $request->validate([
            'date' => 'required|date',
            'nama' => 'required|string|max:255',
            'jam' => 'required|integer|min:2|max:24',
        ]);

        $date = \Carbon\Carbon::parse($validated['date']);
        if ((int) $date->format('n') !== (int) $overtime->bulan || (int) $date->format('Y') !== (int) $overtime->tahun) {
            return response()->json(['success' => false, 'message' => 'Tanggal harus berada dalam bulan lembur ini.'], 422);
        }

        $nama = $this->cleanCell($validated['nama']);
        if ($nama === '') {
            return response()->json(['success' => false, 'message' => 'Nama petugas tidak boleh kosong.'], 422);
        }

        $day = (int) $date->format('j');

        $employee = Employee::where('tipe', Employee::TIPE_KEBERSIHAN)
            ->get()
            ->first(fn ($e) => mb_strtolower(trim($e->nama)) === mb_strtolower($nama));

        $created = false;
        if (!$employee) {
            $employee = Employee::create(['nama' => $nama, 'tipe' => Employee::TIPE_KEBERSIHAN]);
            $created = true;
        }

        $detail = OvertimeDetail::firstOrCreate([
            'overtime_id' => $overtime->id,
            'employee_id' => $employee->id,
        ], [
            'daily_hours' => [],
            'use_uang_makan' => false,
        ]);

        $dailyHours = $detail->daily_hours ?? [];
        $sudahAda = array_key_exists($day, $dailyHours);
        $dailyHours[$day] = (int) $validated['jam'];
        $detail->daily_hours = $dailyHours;
        $detail->save();

        return response()->json([
            'success' => true,
            'employee' => ['id' => $employee->id, 'nama' => $employee->nama],
            'jam' => (int) $validated['jam'],
            'created' => $created,
            'updated' => $sudahAda, // nama sudah tercatat di tanggal ini → jamnya ditimpa
        ]);
    }

    public function importTemplate(Package $package, Overtime $overtime)
    {
        $this->authorizeOvertime($package, $overtime);

        // Template dikirim sebagai .xlsx asli — CSV berkoma tampil menumpuk
        // di satu kolom pada Excel dengan regional Indonesia (pemisah titik koma).
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Kehadiran Lembur');

        $headers = ['No.', 'Nama Pegawai', 'NIP', 'Jabatan', 'Jam'];
        $sheet->fromArray($headers, null, 'A1');

        $contoh = [
            [1, 'BUDI SANTOSO', '199001012025211001', 'PENGADMINISTRASI PERKANTORAN (PPPK)', 5],
            [2, 'SITI AMINAH', '199202022025212002', 'PENGELOLA LAYANAN OPERASIONAL (PPPK)', 4],
            [3, 'AGUS SALIM', '199303032025211003', 'PENGADMINISTRASI PERKANTORAN (PPPK)', 0],
        ];

        $baris = 2;
        foreach ($contoh as $c) {
            $sheet->setCellValue('A' . $baris, $c[0]);
            $sheet->setCellValue('B' . $baris, $c[1]);
            // NIP ditulis eksplisit sebagai TEKS. Kalau dibiarkan jadi angka,
            // NIP 18 digit melampaui presisi Excel dan berubah menjadi
            // notasi ilmiah (digit belakang rusak) saat file dibuka & disimpan.
            $sheet->setCellValueExplicit('C' . $baris, $c[2], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('D' . $baris, $c[3]);
            $sheet->setCellValue('E' . $baris, $c[4]);
            $baris++;
        }

        // Format teks diterapkan di tingkat KOLOM (bukan rentang sel), supaya
        // NIP yang diketik pengguna juga tidak dikonversi Excel tanpa membuat
        // ratusan baris kosong ikut tertulis ke file.
        $sheet->getStyle('C:C')
            ->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

        $sheet->getStyle('A1:E1')->getFont()->setBold(true);
        $sheet->getStyle('A1:E1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E0F2FE');
        $sheet->freezePane('A2');

        // Kolom A dilebarkan tetap: auto-lebar akan ikut menghitung teks
        // catatan panjang di bawah dan membuat kolom "No." jadi sangat lebar.
        $sheet->getColumnDimension('A')->setWidth(6);
        foreach (range('B', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Catatan singkat di bawah contoh agar pengisi paham aturannya.
        $catatanBaris = count($contoh) + 3;
        $sheet->setCellValue('A' . $catatanBaris, 'Catatan: kolom "Jam" diisi jam lembur BERSIH (sudah dikurangi keterlambatan/pulang awal). Kosong atau 0 = tidak lembur. Minimal 2 jam.');
        $sheet->getStyle('A' . $catatanBaris)->getFont()->setItalic(true)->getColor()->setRGB('64748B');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'template-kehadiran-lembur.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Baca baris kehadiran dari file xlsx/csv.
     *
     * Header dikenali di baris pertama (Nama Pegawai / NIP / Jabatan / Jam);
     * kolom lain seperti No., TK, TAP, TL diabaikan. Tanpa header, kolom
     * pertama dianggap nama dan kolom kedua jam.
     *
     * @return array<int, array{nama: string|null, jam: string|null, nip: string|null, jabatan: string|null}>
     */
    protected function readAttendanceRows(\Illuminate\Http\UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension === 'xlsx') {
            $raw = (new \App\Services\XlsxReader())->readRows($file->getRealPath());
        } else {
            $raw = $this->readCsvRows($file->getRealPath());
        }

        // Tanpa header: kolom 0 = nama, kolom 1 = jam.
        $idx = ['nama' => 0, 'jam' => 1, 'nip' => null, 'jabatan' => null];
        $rows = [];

        foreach ($raw as $i => $row) {
            $cells = array_map(fn ($c) => $c === null ? null : $this->cleanCell((string) $c), $row);

            // Deteksi baris header hanya pada baris pertama, agar nama data
            // yang kebetulan memuat kata "petugas/pegawai" tidak ikut dibuang.
            if ($i === 0) {
                $found = [];
                foreach ($cells as $col => $cell) {
                    $lower = mb_strtolower((string) $cell);
                    $key = match (true) {
                        in_array($lower, ['nama', 'nama pegawai', 'nama petugas'], true) => 'nama',
                        in_array($lower, ['jam', 'jam lembur'], true) => 'jam',
                        $lower === 'nip' => 'nip',
                        $lower === 'jabatan' => 'jabatan',
                        default => null,
                    };
                    if ($key !== null && !isset($found[$key])) {
                        $found[$key] = $col;
                    }
                }

                if ($found !== []) {
                    // Header ditemukan: hanya kolom yang benar-benar ada yang dipakai,
                    // supaya file tanpa kolom Jam tidak salah membaca kolom lain.
                    $idx = [
                        'nama' => $found['nama'] ?? 0,
                        'jam' => $found['jam'] ?? null,
                        'nip' => $found['nip'] ?? null,
                        'jabatan' => $found['jabatan'] ?? null,
                    ];
                    continue;
                }
            }

            $entry = [
                'nama' => $cells[$idx['nama']] ?? null,
                'jam' => $idx['jam'] !== null ? ($cells[$idx['jam']] ?? null) : null,
                'nip' => $idx['nip'] !== null ? ($cells[$idx['nip']] ?? null) : null,
                'jabatan' => $idx['jabatan'] !== null ? ($cells[$idx['jabatan']] ?? null) : null,
            ];

            // Lewati baris yang benar-benar kosong (sisa baris berformat di
            // bawah data, atau baris catatan di kolom yang tidak dibaca) agar
            // tidak dilaporkan sebagai "baris tanpa nama".
            if (implode('', array_map(fn ($v) => (string) $v, $entry)) === '') {
                continue;
            }

            $rows[] = $entry;
        }

        return $rows;
    }

    /**
     * Baca CSV dengan pemisah otomatis. Excel dengan regional Indonesia
     * menyimpan CSV memakai titik koma, bukan koma.
     *
     * @return array<int, array<int, string|null>>
     */
    protected function readCsvRows(string $path): array
    {
        $content = file_get_contents($path);

        if ($content === false) {
            throw new \RuntimeException('File CSV tidak dapat dibuka.');
        }

        // Buang BOM UTF-8 dan baris petunjuk "sep=;" bawaan Excel.
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content) ?? $content;
        $content = preg_replace('/^sep=.*(\r\n|\n|\r)/i', '', $content) ?? $content;

        $firstLine = strtok($content, "\r\n");
        $firstLine = $firstLine === false ? '' : $firstLine;

        $candidates = [
            ';' => substr_count($firstLine, ';'),
            ',' => substr_count($firstLine, ','),
            "\t" => substr_count($firstLine, "\t"),
        ];
        arsort($candidates);
        $delimiter = (string) array_key_first($candidates);
        if ($candidates[$delimiter] === 0) {
            $delimiter = ',';
        }

        $rows = [];
        $handle = fopen('php://temp', 'r+');

        if ($handle === false) {
            throw new \RuntimeException('Gagal memproses isi file CSV.');
        }

        fwrite($handle, $content);
        rewind($handle);

        while (($line = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rows[] = $line;
        }

        fclose($handle);

        return $rows;
    }

    /**
     * Buang karakter tak terlihat (zero-width) yang sering ikut saat menyalin
     * data dari Excel/Word — mis. NIP yang diawali U+200C.
     */
    protected function cleanCell(string $value): string
    {
        return trim(preg_replace('/[\x{200B}-\x{200D}\x{FEFF}\x{00A0}]/u', '', $value) ?? $value);
    }

    public function resetMonth(Request $request, Package $package, Overtime $overtime)
    {
        $this->authorizeOvertime($package, $overtime);

        if ($overtime->is_locked) {
            return response()->json(['success' => false, 'message' => 'Data sudah dikunci.']);
        }

        if ($pesan = $this->editDeniedMessage($overtime)) {
            return response()->json(['success' => false, 'message' => $pesan], 403);
        }
        // Reset all daily_hours to empty array for this month
        $details = OvertimeDetail::where('overtime_id', $overtime->id)->get();
        foreach($details as $detail) {
            $detail->daily_hours = [];
            $detail->use_uang_makan = false;
            $detail->save();
        }
        return response()->json(['success' => true]);
    }

    public function updateAjax(Request $request, Package $package, Overtime $overtime)
    {
        $this->authorizeOvertime($package, $overtime);

        if ($overtime->is_locked) {
            return response()->json(['success' => false, 'message' => 'Data sudah dikunci.']);
        }

        if ($pesan = $this->editDeniedMessage($overtime)) {
            return response()->json(['success' => false, 'message' => $pesan], 403);
        }

        $employeeId = $request->input('employee_id');
        $dateStr = $request->input('date'); // YYYY-MM-DD
        $hours = (int) $request->input('hours', 0);
        $useUangMakan = filter_var($request->input('use_uang_makan', false), FILTER_VALIDATE_BOOLEAN);
        $action = $request->input('action', 'update');

        // Aturan lembur: minimal 2 jam dalam 1 hari, maksimal 24.
        if ($action === 'update' && ($hours < 2 || $hours > 24)) {
            return response()->json(['success' => false, 'message' => 'Jam lembur minimal 2 dan maksimal 24 per hari.'], 422);
        }

        if($action === 'save_dasar') {
            $overtime->dasar_pelaksanaan = $request->input('dasar_pelaksanaan');
            $overtime->save();
            return response()->json(['success' => true]);
        }
        
        $day = (int) date('j', strtotime($dateStr));
        
        $detail = OvertimeDetail::where('overtime_id', $overtime->id)
                    ->where('employee_id', $employeeId)
                    ->first();
                    
        if(!$detail) {
            return response()->json(['success' => false, 'message' => 'Pegawai tidak ditemukan di paket ini']);
        }
        
        // Cek bentrok dengan Perjalanan Dinas jika input jam > 0
        if($action === 'update' && $hours > 0) {
            $isOnTravel = \App\Models\TravelPersonnel::where('employee_id', $employeeId)
                ->whereHas('travelOrder', function($q) use ($dateStr) {
                    $q->where('tanggal_berangkat', '<=', $dateStr)
                      ->where('tanggal_kembali', '>=', $dateStr);
                })->exists();
                
            if($isOnTravel) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Pegawai tidak bisa lembur karena sedang melaksanakan Perjalanan Dinas pada tanggal tersebut.'
                ], 422);
            }
        }
        
        $dailyHours = $detail->daily_hours ?? [];
        
        if($action === 'delete') {
            unset($dailyHours[$day]);
        } else {
            $dailyHours[$day] = $hours;
            $detail->use_uang_makan = $useUangMakan; // apply to entire month
        }
        
        $detail->daily_hours = $dailyHours;
        $detail->save();
        
        return response()->json(['success' => true]);
    }

    public function autoFill(Request $request, Package $package, Overtime $overtime)
    {
        $this->authorizeOvertime($package, $overtime);

        if ($overtime->is_locked) {
            return response()->json(['success' => false, 'message' => 'Data sudah dikunci.']);
        }

        if ($pesan = $this->editDeniedMessage($overtime)) {
            return response()->json(['success' => false, 'message' => $pesan], 403);
        }

        // Auto fill for selected employees
        $employeeIds = $request->input('employee_ids', []);
        $holidays = $request->input('holidays', []); // Array of date strings 'YYYY-MM-DD'
        
        $year = $overtime->tahun;
        $month = $overtime->bulan;
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        
        // Cek data Perjalanan Dinas untuk pegawai-pegawai ini di bulan tersebut
        $travels = \App\Models\TravelPersonnel::whereIn('employee_id', $employeeIds)
            ->whereHas('travelOrder', function($q) use ($year, $month, $daysInMonth) {
                $q->where(function($q2) use ($year, $month) {
                    $q2->whereMonth('tanggal_berangkat', $month)
                       ->whereYear('tanggal_berangkat', $year);
                })->orWhere(function($q2) use ($year, $month) {
                    $q2->whereMonth('tanggal_kembali', $month)
                       ->whereYear('tanggal_kembali', $year);
                })->orWhere(function($q2) use ($year, $month, $daysInMonth) {
                    // Atau perjalanannya melintasi bulan ini (berangkat bulan lalu, kembali bulan depan)
                    $q2->where('tanggal_berangkat', '<', "$year-$month-01")
                       ->where('tanggal_kembali', '>', "$year-$month-$daysInMonth");
                });
            })->with('travelOrder')->get();

        $travelDates = [];
        foreach($travels as $t) {
            $empId = $t->employee_id;
            if(!isset($travelDates[$empId])) {
                $travelDates[$empId] = [];
            }
            
            $start = \Carbon\Carbon::parse($t->travelOrder->tanggal_berangkat);
            $end = \Carbon\Carbon::parse($t->travelOrder->tanggal_kembali);
            
            for($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $travelDates[$empId][] = $date->format('Y-m-d');
            }
        }
        
        foreach($employeeIds as $empId) {
            $detail = OvertimeDetail::where('overtime_id', $overtime->id)
                        ->where('employee_id', $empId)
                        ->first();
                        
            if($detail) {
                $dailyHours = $detail->daily_hours ?? [];
                
                for($d = 1; $d <= $daysInMonth; $d++) {
                    $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
                    
                    // Jika tanggal ini ada di daftar dinas luar pegawai, lewati (kosongkan)
                    if(isset($travelDates[$empId]) && in_array($dateStr, $travelDates[$empId])) {
                        unset($dailyHours[$d]);
                        continue;
                    }
                    
                    $dayOfWeek = date('N', strtotime($dateStr));
                    
                    $isWeekend = ($dayOfWeek == 6 || $dayOfWeek == 7);
                    $isHoliday = in_array($dateStr, $holidays);
                    
                    if($isWeekend || $isHoliday) {
                        $dailyHours[$d] = 5;
                    } else {
                        $dailyHours[$d] = 2;
                    }
                }
                
                $detail->daily_hours = $dailyHours;
                $detail->save();
            }
        }
        
        return response()->json(['success' => true]);
    }

    public function update(Request $request, Package $package, Overtime $overtime)
    {
        $this->authorizeOvertime($package, $overtime);

        if ($overtime->is_locked) {
            return redirect()->back()->with('error', 'Data sudah dikunci.');
        }

        if ($pesan = $this->editDeniedMessage($overtime)) {
            return redirect()->back()->with('error', $pesan);
        }

        $details = $request->input('details', []);
        
        foreach ($details as $detailId => $data) {
            $detail = OvertimeDetail::find($detailId);
            if ($detail && $detail->overtime_id == $overtime->id) {
                $detail->update([
                    'daily_hours' => $data['daily_hours'] ?? [],
                    'use_uang_makan' => isset($data['use_uang_makan']) ? (bool) $data['use_uang_makan'] : false,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Data lembur berhasil disimpan.');
    }

    public function print(Package $package, Overtime $overtime, $type)
    {
        $this->authorizeOvertime($package, $overtime);

        $sbuRates = \App\Models\SbuLembur::all();
        
        $year = $overtime->tahun;
        $month = $overtime->bulan;
        $holidays = \App\Models\Holiday::whereYear('holiday_date', $year)
                                       ->whereMonth('holiday_date', $month)
                                       ->pluck('holiday_date')
                                       ->toArray();
        
        $overtime->load('details.employee');
        $skpd = \App\Models\Skpd::first();
        
        if ($type == 'rekap') {
            return view('overtimes.print_rekap', compact('package', 'overtime', 'sbuRates', 'holidays', 'skpd'));
        } elseif ($type == 'tanda_terima') {
            return view('overtimes.print_tanda_terima', compact('package', 'overtime', 'sbuRates', 'skpd'));
        } elseif ($type == 'kwitansi') {
            return view('overtimes.print_kwitansi', compact('package', 'overtime', 'sbuRates', 'skpd'));
        }

        return abort(404);
    }

    /**
     * Cetak dokumen SPJ lembur kebersihan utk satu PERIODE (rentang bulan,
     * boleh satu bulan). Semua bulan dalam rentang WAJIB bermode kebersihan
     * dan sudah dikunci — supaya angka dokumen dijamin sama dengan sistem.
     *
     * $type: rekap (absensi per bulan) | tanda_terima (gabungan) | kwitansi (total periode)
     */
    public function printSpj(Request $request, Package $package, $type)
    {
        Gate::authorize('view', $package);
        abort_unless(in_array($type, ['rekap', 'tanda_terima', 'kwitansi'], true), 404);

        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $dari = (int) $request->query('dari');
        $sampai = (int) $request->query('sampai');
        if ($dari < 1 || $dari > 12 || $sampai < $dari || $sampai > 12) {
            return response('<p style="font-family:sans-serif;padding:24px">Periode SPJ tidak valid.</p>', 422);
        }

        $year = $package->created_at ? $package->created_at->format('Y') : date('Y');

        $spjOvertimes = [];
        $masalah = [];
        for ($m = $dari; $m <= $sampai; $m++) {
            $ot = Overtime::with('details.employee')
                ->where('package_id', $package->id)
                ->where('bulan', $m)
                ->where('tahun', $year)
                ->first();

            if (!$ot || $ot->jenis_lembur !== 'kebersihan') {
                $masalah[] = "bulan {$namaBulan[$m]} belum diatur sebagai lembur petugas kebersihan";
            } elseif (!$ot->is_locked) {
                $masalah[] = "bulan {$namaBulan[$m]} belum dikunci";
            } else {
                $spjOvertimes[] = $ot;
            }
        }

        if ($masalah !== []) {
            return response(
                '<div style="font-family:sans-serif;padding:24px;max-width:560px">'
                . '<h3 style="margin:0 0 8px">SPJ tidak dapat dibuat</h3>'
                . '<p>Seluruh bulan dalam periode harus bermode Petugas Kebersihan dan sudah <b>dikunci</b>:</p>'
                . '<ul><li>' . implode('</li><li>', array_map('e', $masalah)) . '</li></ul>'
                . '<p style="color:#64748b">Kunci bulan tersebut lalu coba lagi.</p></div>',
                422
            );
        }

        $sbuRates = \App\Models\SbuLembur::all();
        $skpd = \App\Models\Skpd::first();
        $periodeLabel = $dari === $sampai
            ? "Bulan {$namaBulan[$dari]} Tahun {$year}"
            : "Bulan {$namaBulan[$dari]} s.d. {$namaBulan[$sampai]} Tahun {$year}";
        $overtime = $spjOvertimes[0]; // konteks dasar_pelaksanaan / tahun utk view

        $pembuatId = $request->query('pembuat_id');
        $pembuat = $pembuatId ? \App\Models\Employee::find($pembuatId) : null;

        if ($type === 'rekap') {
            $selectedMonths = [];
            for ($m = $dari; $m <= $sampai; $m++) {
                $selectedMonths[$m] = strtoupper($namaBulan[$m]);
            }

            $mergedRecap = [];
            foreach ($spjOvertimes as $ot) {
                $rekap = $ot->rekap($sbuRates);
                foreach ($rekap['rows'] as $row) {
                    $empId = $row['employee']->id;
                    if (!isset($mergedRecap[$empId])) {
                        $mergedRecap[$empId] = [
                            'employee' => $row['employee'],
                            'jabatan' => $row['employee']->jabatan ?? 'STAF',
                            'monthlyHours' => [],
                            'totalJam' => 0,
                        ];
                    }
                    $mergedRecap[$empId]['monthlyHours'][$ot->bulan] = $row['totalJam'];
                    $mergedRecap[$empId]['totalJam'] += $row['totalJam'];
                }
            }

            $rekapRows = array_values($mergedRecap);
            usort($rekapRows, fn ($a, $b) => strcmp($a['employee']->nama, $b['employee']->nama));

            return view('overtimes.spj_rekap', compact(
                'package', 'spjOvertimes', 'skpd', 'periodeLabel',
                'selectedMonths', 'rekapRows', 'pembuat', 'dari', 'sampai', 'year'
            ));
        }

        // Gabungkan hasil rekap() lintas bulan per pegawai.
        $merged = [];
        $spjTotalUpah = 0;
        $spjTotalPajak = 0;
        foreach ($spjOvertimes as $ot) {
            $rekap = $ot->rekap($sbuRates);
            $spjTotalUpah += $rekap['totalUpah'];
            $spjTotalPajak += $rekap['totalPajak'];

            foreach ($rekap['rows'] as $row) {
                $id = $row['employee']->id;
                if (!isset($merged[$id])) {
                    $merged[$id] = [
                        'employee' => $row['employee'],
                        'golongan' => $row['golongan'],
                        'totalJam' => 0,
                        'uangLembur' => 0,
                        'pajak' => 0,
                        'rates' => [],
                    ];
                }
                $merged[$id]['totalJam'] += $row['totalJam'];
                $merged[$id]['uangLembur'] += $row['uangLembur'];
                $merged[$id]['pajak'] += $row['pajak'];
                $merged[$id]['rates'][] = (float) $row['valLembur'];
            }
        }

        $spjRows = array_values($merged);
        usort($spjRows, fn ($a, $b) => strcmp($a['employee']->nama, $b['employee']->nama));
        foreach ($spjRows as &$row) {
            // Tarif ditampilkan hanya bila konsisten sepanjang periode.
            $unik = array_unique($row['rates']);
            $row['valLembur'] = count($unik) === 1 ? $unik[0] : null;
            unset($row['rates']);
        }
        unset($row);

        if ($type === 'tanda_terima') {
            return view('overtimes.print_tanda_terima', compact(
                'package', 'overtime', 'sbuRates', 'skpd',
                'spjRows', 'spjTotalUpah', 'spjTotalPajak', 'periodeLabel', 'pembuat'
            ));
        }

        return view('overtimes.print_kwitansi', compact(
            'package', 'overtime', 'sbuRates', 'skpd',
            'spjTotalUpah', 'spjTotalPajak', 'periodeLabel', 'pembuat'
        ));
    }

    public function updateRates(Request $request, Package $package, Overtime $overtime, OvertimeDetail $detail)
    {
        $this->authorizeOvertime($package, $overtime);
        abort_unless((int) $detail->overtime_id === (int) $overtime->getKey(), 404);

        if ($overtime->is_locked) {
            return redirect()->back()->with('error', 'Data lembur sudah dikunci.');
        }

        if ($pesan = $this->editDeniedMessage($overtime)) {
            return redirect()->back()->with('error', $pesan);
        }

        $request->validate([
            'rate_lembur_fix' => 'nullable|numeric|min:0',
            'rate_makan_fix' => 'nullable|numeric|min:0',
        ]);

        $detail->update([
            'rate_lembur_fix' => $request->rate_lembur_fix,
            'rate_makan_fix' => $request->rate_makan_fix,
        ]);

        return redirect()->back()->with('success', 'Standar Biaya (SBU) berhasil diperbarui.');
    }



    public function lock(Request $request, Package $package, $month)
    {
        Gate::authorize('view', $package);

        $year = $package->created_at ? $package->created_at->format('Y') : date('Y');
        $overtime = Overtime::where('package_id', $package->id)
            ->where('bulan', $month)
            ->where('tahun', $year)
            ->firstOrFail();

        $userRole = auth()->user()->getRoleNames()->first() ?? '';
        if (in_array($userRole, ['Admin', 'Kabid'])) {
            $sbuRates = \App\Models\SbuLembur::all();
            
            foreach ($overtime->details as $detail) {
                $golongan = $detail->employee->golongan ?? null;

                $updateData = [];
                $updateData['golongan_fix'] = $golongan ?? '-';

                if (is_null($detail->rate_lembur_fix)) {
                    $updateData['rate_lembur_fix'] = \App\Models\SbuLembur::pickRate($sbuRates, 'Uang Lembur', $golongan)?->besaran ?? 0;
                }

                if (is_null($detail->rate_makan_fix)) {
                    $updateData['rate_makan_fix'] = \App\Models\SbuLembur::pickRate($sbuRates, 'Uang Makan Lembur', $golongan)?->besaran ?? 0;
                }

                $detail->update($updateData);
            }

            $overtime->update(['is_locked' => true]);
            return redirect()->back()->with('success', 'Data lembur bulan ini berhasil dikunci dan snapshot telah disimpan permanen.');
        }

        return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengunci data.');
    }

    public function unlock(Request $request, Package $package, $month)
    {
        Gate::authorize('view', $package);

        $year = $package->created_at ? $package->created_at->format('Y') : date('Y');
        $overtime = Overtime::where('package_id', $package->id)
            ->where('bulan', $month)
            ->where('tahun', $year)
            ->firstOrFail();

        $userRole = auth()->user()->getRoleNames()->first() ?? '';
        if ($userRole === 'Admin') {
            $overtime->update(['is_locked' => false]);
            return redirect()->back()->with('success', 'Kunci data berhasil dibuka. Data dapat diedit kembali.');
        }

        return redirect()->back()->with('error', 'Hanya Admin yang dapat membuka kunci data.');
    }

    private function authorizeOvertime(Package $package, Overtime $overtime): void
    {
        Gate::authorize('view', $package);
        abort_unless((int) $overtime->package_id === (int) $package->getKey(), 404);
    }

    /**
     * Guard peran untuk aksi TULIS pada data lembur.
     * - Kabid: baca-saja pada mode kebersihan (input oleh Staf/Admin).
     * - Staff: hanya boleh menulis pada mode kebersihan.
     * Mengembalikan pesan penolakan, atau null bila diizinkan.
     */
    protected function editDeniedMessage(Overtime $overtime): ?string
    {
        $role = auth()->user()?->getRoleNames()->first() ?? '';

        if ($role === 'Kabid' && $overtime->jenis_lembur === 'kebersihan') {
            return 'Lembur petugas kebersihan diinput oleh Staf/Admin — Kabid hanya dapat melihat dan mengunci data.';
        }

        if ($role === 'Staff' && $overtime->jenis_lembur !== 'kebersihan') {
            return 'Staf hanya dapat mengelola lembur petugas kebersihan.';
        }

        return null;
    }
}
