<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BudgetLineRequest;
use App\Models\Account;
use App\Models\BudgetLine;
use App\Models\BudgetRevision;
use App\Models\FiscalYear;
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
    public function index(Request $request): View
    {
        $fiscalYears = FiscalYear::orderByDesc('tahun')->get();
        $tahunId = $request->tahun ?: ($fiscalYears->firstWhere('is_active', true)?->id ?? $fiscalYears->first()?->id);
        $programId = $request->program;
        $search = $request->q;
        $hanyaSelisih = $request->boolean('selisih');

        $lines = BudgetLine::query()
            ->with(['subActivity.activity.program', 'account', 'revisions'])
            ->when($tahunId, fn ($q) => $q->where('fiscal_year_id', $tahunId))
            ->when($programId, fn ($q) => $q->whereHas(
                'subActivity.activity',
                fn ($a) => $a->where('program_id', $programId)
            ))
            ->when($search, fn ($q) => $q->where(fn ($sub) => $sub
                ->whereHas('account', fn ($a) => $a->where('kode', 'like', "%{$search}%")->orWhere('nama', 'like', "%{$search}%"))
                ->orWhereHas('subActivity', fn ($s) => $s->where('kode', 'like', "%{$search}%")->orWhere('nama', 'like', "%{$search}%"))))
            ->get()
            ->sortBy(fn ($l) => [$l->subActivity?->kode, $l->account?->kode])
            ->values();

        // Satu query untuk seluruh rekonsiliasi — hindari N+1 pada daftar.
        $rekon = BudgetLine::rekonsiliasiMap($tahunId ?: null);

        $baris = $lines->map(function (BudgetLine $line) use ($rekon) {
            $data = $rekon->get($line->kunciSel(), ['total' => 0.0, 'jumlah' => 0]);

            return [
                'line' => $line,
                'terinput' => $data['total'],
                'jumlahPaket' => $data['jumlah'],
                'selisih' => $line->selisih($data['total']),
            ];
        });

        if ($hanyaSelisih) {
            $baris = $baris->filter(fn ($b) => abs($b['selisih']) >= 0.01)->values();
        }

        $ringkas = [
            'baris' => $baris->count(),
            'plafon' => $baris->sum(fn ($b) => (float) $b['line']->pagu_efektif),
            'terinput' => $baris->sum('terinput'),
            'belumSeimbang' => $baris->filter(fn ($b) => abs($b['selisih']) >= 0.01)->count(),
        ];

        return view('anggaran.index', [
            'baris' => $baris,
            'ringkas' => $ringkas,
            'fiscalYears' => $fiscalYears,
            'programs' => Program::orderBy('kode')->get(),
            'tahunId' => $tahunId,
            'programId' => $programId,
            'search' => $search,
            'hanyaSelisih' => $hanyaSelisih,
        ]);
    }

    public function create(): View
    {
        return view('anggaran.create', $this->formData(new BudgetLine([
            'fiscal_year_id' => FiscalYear::where('is_active', true)->value('id'),
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
            ->route('admin.anggaran.index')
            ->with('success', 'Baris anggaran berhasil ditambahkan.');
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
