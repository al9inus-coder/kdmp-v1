<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Satu baris DPA = satu rekening belanja di dalam satu sub kegiatan,
 * pada satu tahun anggaran.
 *
 * Pagu resmi ada di sini (lewat riwayat revisi), sedangkan pagu paket RUP
 * adalah rinciannya. Selisih keduanya menjadi kontrol kelengkapan input.
 */
class BudgetLine extends Model
{
    protected $fillable = [
        'fiscal_year_id',
        'sub_activity_id',
        'account_id',
        'pagu_efektif',
        'keterangan',
    ];

    protected $casts = [
        'pagu_efektif' => 'decimal:2',
    ];

    // ── Relasi ────────────────────────────────────────────────

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function subActivity(): BelongsTo
    {
        return $this->belongsTo(SubActivity::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /** Riwayat revisi, urut dari yang paling awal. */
    public function revisions(): HasMany
    {
        return $this->hasMany(BudgetRevision::class)->orderBy('id');
    }

    // ── Nilai pagu ────────────────────────────────────────────

    /**
     * Revisi terakhir menentukan pagu yang berlaku.
     *
     * Catatan: relasi revisions() sudah membawa orderBy('id') menaik, jadi
     * pembalikan urutan WAJIB memakai reorder() — orderByDesc() saja hanya
     * menumpuk jadi "ORDER BY id ASC, id DESC" dan tetap menghasilkan yang
     * paling awal.
     */
    public function latestRevision(): ?BudgetRevision
    {
        return $this->relationLoaded('revisions')
            ? $this->revisions->last()
            : $this->revisions()->reorder('id', 'desc')->first();
    }

    /** Pagu awal tahun (APBD Murni); null bila belum pernah dicatat. */
    public function paguMurni(): ?string
    {
        $murni = $this->relationLoaded('revisions')
            ? $this->revisions->firstWhere('jenis', BudgetRevision::JENIS_MURNI)
            : $this->revisions()->where('jenis', BudgetRevision::JENIS_MURNI)->reorder('id', 'asc')->first();

        return $murni?->pagu;
    }

    /**
     * Selaraskan kolom cache dengan revisi terakhir. Dipanggil setiap kali
     * riwayat berubah, supaya monev cukup membaca satu kolom.
     */
    public function recalcPaguEfektif(): void
    {
        $this->pagu_efektif = $this->revisions()->reorder('id', 'desc')->value('pagu') ?? 0;
        $this->save();
    }

    // ── Riwayat berdampingan (kolom tahap) ────────────────────

    /**
     * Susun kolom tahap yang benar-benar dipakai sekumpulan baris:
     * Murni, Pergeseran I..N (sebanyak yang ada), lalu Perubahan.
     *
     * @param  \Illuminate\Support\Collection<int, BudgetLine>  $lines
     * @return array<int, array{kunci: string, label: string}>
     */
    public static function kolomTahap($lines): array
    {
        $revisi = $lines->flatMap(fn ($l) => $l->revisions);

        $maxPergeseran = (int) $revisi
            ->where('jenis', BudgetRevision::JENIS_PERGESERAN)
            ->max('urutan');

        $kolom = [['kunci' => BudgetRevision::JENIS_MURNI, 'label' => 'Murni']];

        $romawi = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'];
        for ($i = 1; $i <= $maxPergeseran; $i++) {
            $kolom[] = [
                'kunci' => BudgetRevision::JENIS_PERGESERAN . '-' . $i,
                'label' => 'Pergeseran ' . ($romawi[$i] ?? $i),
            ];
        }

        if ($revisi->contains('jenis', BudgetRevision::JENIS_PERUBAHAN)) {
            $kolom[] = ['kunci' => BudgetRevision::JENIS_PERUBAHAN, 'label' => 'Perubahan'];
        }

        return $kolom;
    }

    /**
     * Nilai pagu pada tiap tahap. Tahap yang tidak direvisi mewarisi nilai
     * tahap sebelumnya (pagunya memang tidak berubah) — ditandai
     * 'eksplisit' => false agar tampilannya bisa diredupkan.
     *
     * @param  array<int, array{kunci: string, label: string}>  $kolom
     * @return array<string, array{nilai: ?float, eksplisit: bool}>
     */
    public function nilaiPerTahap(array $kolom): array
    {
        $hasil = [];
        $berjalan = null;

        foreach ($kolom as $k) {
            $rev = $this->revisions->first(fn ($r) => $r->kunciTahap() === $k['kunci']);

            if ($rev) {
                $berjalan = (float) $rev->pagu;
            }

            $hasil[$k['kunci']] = ['nilai' => $berjalan, 'eksplisit' => (bool) $rev];
        }

        return $hasil;
    }

    // ── Rekonsiliasi dengan paket RUP ─────────────────────────

    /**
     * Paket RUP yang jatuh pada sel ini. Bukan relasi Eloquent karena
     * pencocokannya memakai tiga kunci sekaligus.
     */
    public function packagesQuery(): Builder
    {
        return Package::query()
            ->where('fiscal_year_id', $this->fiscal_year_id)
            ->where('sub_activity_id', $this->sub_activity_id)
            ->where('account_id', $this->account_id);
    }

    public function kunciSel(): string
    {
        return static::kunci($this->fiscal_year_id, $this->sub_activity_id, $this->account_id);
    }

    public static function kunci(?int $fiscalYearId, ?int $subActivityId, ?int $accountId): string
    {
        return $fiscalYearId . '-' . $subActivityId . '-' . $accountId;
    }

    /**
     * Total pagu paket per sel dalam SATU query — dipakai halaman daftar
     * agar tidak menembak database sekali per baris.
     *
     * @return Collection<string, array{total: float, jumlah: int}>
     */
    public static function rekonsiliasiMap(?int $fiscalYearId = null): Collection
    {
        return Package::query()
            ->whereNotNull('sub_activity_id')
            ->whereNotNull('account_id')
            ->when($fiscalYearId, fn ($q) => $q->where('fiscal_year_id', $fiscalYearId))
            ->selectRaw('fiscal_year_id, sub_activity_id, account_id, SUM(pagu) as total, COUNT(*) as jumlah')
            ->groupBy('fiscal_year_id', 'sub_activity_id', 'account_id')
            ->get()
            ->mapWithKeys(fn ($row) => [
                static::kunci($row->fiscal_year_id, $row->sub_activity_id, $row->account_id) => [
                    'total' => (float) $row->total,
                    'jumlah' => (int) $row->jumlah,
                ],
            ]);
    }

    /** Total pagu paket pada sel ini (untuk satu baris; daftar pakai rekonsiliasiMap). */
    public function terinput(): float
    {
        return (float) $this->packagesQuery()->sum('pagu');
    }

    /**
     * Positif  = ada pagu yang belum dirinci jadi paket (paket belum diinput).
     * Negatif  = rincian paket melebihi plafon DPA (over-input).
     */
    public function selisih(?float $terinput = null): float
    {
        return (float) $this->pagu_efektif - ($terinput ?? $this->terinput());
    }
}
