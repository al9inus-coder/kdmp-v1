<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BudgetLineRequest;
use App\Models\Account;
use App\Models\BudgetLine;
use App\Models\BudgetRevision;
use App\Models\FiscalYear;
use App\Models\Package;
use App\Models\Program;
use App\Models\SubActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Master Anggaran (DPA): plafon per rekening belanja dalam sub kegiatan,
 * beserta riwayat revisinya (murni -> pergeseran -> perubahan).
 */
class BudgetLineController extends Controller
{
    /**
     * Tingkat 1 — gambaran per program.
     *
     * Mengikuti pola monev: kartu Program → dikelompokkan per Kegiatan →
     * grid kartu Sub Kegiatan. Angka yang ditonjolkan adalah plafon DPA vs
     * yang sudah dirinci jadi paket RUP.
     */
    public function index(Request $request): View
    {
        $fiscalYears = FiscalYear::orderByDesc('tahun')->get();
        $tahunId = $request->tahun ?: ($fiscalYears->firstWhere('is_active', true)?->id ?? $fiscalYears->first()?->id);

        // Semua baris anggaran tahun ini, dikelompokkan per sub kegiatan.
        $linesPerSub = BudgetLine::query()
            ->where('fiscal_year_id', $tahunId)
            ->get()
            ->groupBy('sub_activity_id');

        // Sub kegiatan yang punya paket tapi belum punya plafon juga perlu
        // terlihat — justru itu yang harus ditindaklanjuti.
        // Hanya paket yang sudah punya rekening yang dibandingkan dengan
        // plafon, supaya angkanya setara dengan baris anggaran.
        $paketPerSub = Package::query()
            ->where('fiscal_year_id', $tahunId)
            ->whereNotNull('sub_activity_id')
            ->whereNotNull('account_id')
            ->selectRaw('sub_activity_id, SUM(pagu) as total, COUNT(*) as jumlah')
            ->groupBy('sub_activity_id')
            ->get()
            ->keyBy('sub_activity_id');

        // Paket yang belum dipetakan ke rekening: bukan "melebihi plafon",
        // melainkan pekerjaan pemetaan yang belum selesai.
        $tanpaRekening = Package::query()
            ->where('fiscal_year_id', $tahunId)
            ->whereNotNull('sub_activity_id')
            ->whereNull('account_id')
            ->selectRaw('sub_activity_id, SUM(pagu) as total, COUNT(*) as jumlah')
            ->groupBy('sub_activity_id')
            ->get()
            ->keyBy('sub_activity_id');

        // Paket yang bahkan belum punya sub kegiatan — tidak bisa
        // ditempelkan ke mana pun, ditampilkan sebagai peringatan global.
        $yatim = Package::query()
            ->where('fiscal_year_id', $tahunId)
            ->whereNull('sub_activity_id')
            ->selectRaw('SUM(pagu) as total, COUNT(*) as jumlah')
            ->first();

        // Seluruh sub kegiatan AKTIF ikut tampil walau belum punya rekening —
        // justru dari kartunya itulah plafon pertama diisi. Sub kegiatan
        // non-aktif hanya muncul bila masih menyimpan data.
        $subIds = SubActivity::where('is_active', true)->pluck('id')
            ->merge($linesPerSub->keys())
            ->merge($paketPerSub->keys())
            ->merge($tanpaRekening->keys())
            ->unique();

        $programs = Program::query()
            ->with(['activities' => fn ($q) => $q->orderBy('kode'),
                    'activities.subActivities' => fn ($q) => $q->whereIn('id', $subIds)->orderBy('kode')])
            ->orderBy('kode')
            ->get()
            ->filter(fn ($p) => $p->activities->contains(fn ($a) => $a->subActivities->isNotEmpty()));

        // Ringkasan per sub kegiatan, dipakai kartu di view.
        $ringkasSub = collect($subIds)->mapWithKeys(function ($subId) use ($linesPerSub, $paketPerSub, $tanpaRekening) {
            $lines = $linesPerSub->get($subId, collect());
            $paket = $paketPerSub->get($subId);
            $belum = $tanpaRekening->get($subId);

            $plafon = (float) $lines->sum('pagu_efektif');
            $terinput = (float) ($paket->total ?? 0);

            return [$subId => [
                'jumlahRekening' => $lines->count(),
                'jumlahPaket' => (int) ($paket->jumlah ?? 0),
                'plafon' => $plafon,
                'terinput' => $terinput,
                'selisih' => $plafon - $terinput,
                'adaPlafon' => $lines->isNotEmpty(),
                'tanpaRekeningJumlah' => (int) ($belum->jumlah ?? 0),
                'tanpaRekeningTotal' => (float) ($belum->total ?? 0),
            ]];
        });

        $ringkas = [
            'subKegiatan' => $ringkasSub->count(),
            'rekening' => $ringkasSub->sum('jumlahRekening'),
            'plafon' => $ringkasSub->sum('plafon'),
            'terinput' => $ringkasSub->sum('terinput'),
            'belumSeimbang' => $ringkasSub->filter(fn ($r) => abs($r['selisih']) >= 0.01)->count(),
            'belumAdaPlafon' => $ringkasSub->filter(fn ($r) => !$r['adaPlafon'])->count(),
            'tanpaRekeningJumlah' => $ringkasSub->sum('tanpaRekeningJumlah'),
            'tanpaRekeningTotal' => $ringkasSub->sum('tanpaRekeningTotal'),
            'yatimJumlah' => (int) ($yatim->jumlah ?? 0),
            'yatimTotal' => (float) ($yatim->total ?? 0),
        ];

        return view('anggaran.index', [
            'programs' => $programs,
            'ringkasSub' => $ringkasSub,
            'ringkas' => $ringkas,
            'fiscalYears' => $fiscalYears,
            'tahunId' => $tahunId,
        ]);
    }

    /**
     * Tingkat 2 — rekening di dalam satu sub kegiatan. Di sinilah plafon
     * diubah, karena satu peristiwa APBD-P biasanya menyentuh banyak
     * rekening sekaligus dengan dasar hukum yang sama.
     */
    public function subActivity(Request $request, SubActivity $subActivity): View
    {
        $fiscalYears = FiscalYear::orderByDesc('tahun')->get();
        $tahunId = $request->tahun ?: ($fiscalYears->firstWhere('is_active', true)?->id ?? $fiscalYears->first()?->id);

        $lines = BudgetLine::query()
            ->with(['account', 'revisions'])
            ->where('fiscal_year_id', $tahunId)
            ->where('sub_activity_id', $subActivity->id)
            ->get()
            ->sortBy(fn ($l) => $l->account?->kode)
            ->values();

        $rekon = BudgetLine::rekonsiliasiMap($tahunId);

        $baris = $lines->map(function (BudgetLine $line) use ($rekon) {
            $data = $rekon->get($line->kunciSel(), ['total' => 0.0, 'jumlah' => 0]);

            return [
                'line' => $line,
                'terinput' => $data['total'],
                'jumlahPaket' => $data['jumlah'],
                'selisih' => $line->selisih($data['total']),
            ];
        });

        $subActivity->load('activity.program');

        return view('anggaran.sub-kegiatan', [
            'subActivity' => $subActivity,
            'baris' => $baris,
            'fiscalYears' => $fiscalYears,
            'tahunId' => $tahunId,
            'tahun' => $fiscalYears->firstWhere('id', $tahunId),
            'accounts' => Account::orderBy('kode')->get(),
            'jenisOptions' => BudgetRevision::jenisOptions(),
            'ringkas' => [
                'plafon' => $baris->sum(fn ($b) => (float) $b['line']->pagu_efektif),
                'terinput' => $baris->sum('terinput'),
                'belumSeimbang' => $baris->filter(fn ($b) => abs($b['selisih']) >= 0.01)->count(),
            ],
        ]);
    }

    /**
     * Catat satu peristiwa revisi untuk seluruh rekening yang nilainya
     * berubah — satu dasar hukum, banyak baris. Rekening yang nilainya
     * tetap tidak ikut dicatat agar riwayat tidak penuh entri kosong.
     */
    public function bulkRevision(Request $request, SubActivity $subActivity): RedirectResponse
    {
        $data = $request->validate([
            'jenis' => ['required', Rule::in(array_keys(BudgetRevision::jenisOptions()))],
            'tanggal' => ['nullable', 'date'],
            'nomor_dasar' => ['nullable', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'pagu' => ['required', 'array'],
            'pagu.*' => ['nullable', 'numeric', 'min:0'],
        ], [], ['jenis' => 'tahap anggaran']);

        $berubah = 0;

        DB::transaction(function () use ($data, $subActivity, &$berubah) {
            $lines = BudgetLine::whereIn('id', array_keys($data['pagu']))
                ->where('sub_activity_id', $subActivity->id)
                ->get();

            foreach ($lines as $line) {
                $baru = $data['pagu'][$line->id] ?? null;

                if ($baru === null || abs((float) $baru - (float) $line->pagu_efektif) < 0.01) {
                    continue; // nilainya tidak berubah
                }

                $urutan = $line->revisions()->where('jenis', $data['jenis'])->count() + 1;

                $line->revisions()->create([
                    'jenis' => $data['jenis'],
                    'urutan' => $urutan,
                    'tanggal' => $data['tanggal'] ?? null,
                    'nomor_dasar' => $data['nomor_dasar'] ?? null,
                    'pagu' => $baru,
                    'keterangan' => $data['keterangan'] ?? null,
                    'created_by' => auth()->id(),
                ]);

                $line->refresh()->recalcPaguEfektif();
                $berubah++;
            }
        });

        if ($berubah === 0) {
            return back()->with('error', 'Tidak ada plafon yang berubah — revisi tidak dicatat.');
        }

        return back()->with('success', "Revisi dicatat untuk {$berubah} rekening dengan dasar hukum yang sama.");
    }

    public function create(Request $request): View
    {
        // Dipanggil dari halaman sub kegiatan → sub kegiatannya sudah terisi.
        return view('anggaran.create', $this->formData(new BudgetLine([
            'fiscal_year_id' => $request->tahun ?: FiscalYear::where('is_active', true)->value('id'),
            'sub_activity_id' => $request->sub_kegiatan,
        ])));
    }

    public function store(BudgetLineRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $line = BudgetLine::create([
                'fiscal_year_id' => $data['fiscal_year_id'],
                'sub_activity_id' => $data['sub_activity_id'],
                'account_id' => $data['account_id'],
                'pagu_efektif' => $data['pagu'],
                'keterangan' => $data['keterangan'] ?? null,
            ]);

            $line->revisions()->create([
                'jenis' => $data['jenis'],
                'urutan' => 1,
                'tanggal' => $data['tanggal'] ?? null,
                'nomor_dasar' => $data['nomor_dasar'] ?? null,
                'pagu' => $data['pagu'],
                'created_by' => auth()->id(),
            ]);
        });

        return redirect()
            ->route('admin.anggaran.sub-kegiatan', [$data['sub_activity_id'], 'tahun' => $data['fiscal_year_id']])
            ->with('success', 'Rekening berhasil ditambahkan ke sub kegiatan ini.');
    }

    public function edit(BudgetLine $anggaran): View
    {
        $anggaran->load(['revisions.creator', 'subActivity.activity.program', 'account', 'fiscalYear']);

        return view('anggaran.edit', $this->formData($anggaran) + [
            'terinput' => $anggaran->terinput(),
            'jumlahPaket' => $anggaran->packagesQuery()->count(),
        ]);
    }

    /** Ubah identitas baris & keterangan (bukan nilai pagu — itu lewat revisi). */
    public function update(BudgetLineRequest $request, BudgetLine $anggaran): RedirectResponse
    {
        $data = $request->validated();

        $anggaran->update([
            'fiscal_year_id' => $data['fiscal_year_id'],
            'sub_activity_id' => $data['sub_activity_id'],
            'account_id' => $data['account_id'],
            'keterangan' => $data['keterangan'] ?? null,
        ]);

        return redirect()
            ->route('admin.anggaran.edit', $anggaran)
            ->with('success', 'Data baris anggaran diperbarui.');
    }

    /**
     * Catat revisi baru. Nilai pagu tidak pernah ditimpa diam-diam —
     * selalu tercatat sebagai langkah baru pada riwayat.
     */
    public function storeRevision(Request $request, BudgetLine $anggaran): RedirectResponse
    {
        $data = $request->validate([
            'jenis' => ['required', Rule::in(array_keys(BudgetRevision::jenisOptions()))],
            'pagu' => ['required', 'numeric', 'min:0'],
            'tanggal' => ['nullable', 'date'],
            'nomor_dasar' => ['nullable', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'jenis' => 'tahap anggaran',
            'pagu' => 'pagu',
        ]);

        DB::transaction(function () use ($data, $anggaran) {
            // Pergeseran bisa berulang — beri nomor urut otomatis.
            $urutan = $anggaran->revisions()->where('jenis', $data['jenis'])->count() + 1;

            $anggaran->revisions()->create([
                'jenis' => $data['jenis'],
                'urutan' => $urutan,
                'tanggal' => $data['tanggal'] ?? null,
                'nomor_dasar' => $data['nomor_dasar'] ?? null,
                'pagu' => $data['pagu'],
                'keterangan' => $data['keterangan'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $anggaran->refresh()->recalcPaguEfektif();
        });

        return redirect()
            ->route('admin.anggaran.edit', $anggaran)
            ->with('success', 'Revisi anggaran dicatat. Pagu berlaku diperbarui.');
    }

    /** Batalkan revisi terakhir (mis. salah ketik) — hanya yang paling akhir. */
    public function destroyRevision(BudgetLine $anggaran, BudgetRevision $revision): RedirectResponse
    {
        abort_unless((int) $revision->budget_line_id === (int) $anggaran->id, 404);

        // reorder() wajib: relasi revisions() sudah ber-orderBy menaik.
        $terakhir = $anggaran->revisions()->reorder('id', 'desc')->first();

        if (!$terakhir || $terakhir->id !== $revision->id) {
            return back()->with('error', 'Hanya revisi terakhir yang dapat dibatalkan.');
        }

        if ($anggaran->revisions()->count() <= 1) {
            return back()->with('error', 'Revisi pertama tidak dapat dihapus — hapus barisnya bila memang keliru.');
        }

        DB::transaction(function () use ($revision, $anggaran) {
            $revision->delete();
            $anggaran->refresh()->recalcPaguEfektif();
        });

        return back()->with('success', 'Revisi terakhir dibatalkan.');
    }

    public function destroy(BudgetLine $anggaran): RedirectResponse
    {
        $anggaran->delete(); // revisi ikut terhapus (cascade)

        return redirect()
            ->route('admin.anggaran.index')
            ->with('success', 'Baris anggaran dihapus.');
    }

    private function formData(BudgetLine $anggaran): array
    {
        return [
            'anggaran' => $anggaran,
            'fiscalYears' => FiscalYear::orderByDesc('tahun')->get(),
            'subActivities' => SubActivity::with('activity.program')->orderBy('kode')->get(),
            'accounts' => Account::orderBy('kode')->get(),
            'jenisOptions' => BudgetRevision::jenisOptions(),
        ];
    }
}
