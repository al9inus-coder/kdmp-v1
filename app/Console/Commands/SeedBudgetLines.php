<?php

namespace App\Console\Commands;

use App\Models\BudgetLine;
use App\Models\BudgetRevision;
use App\Models\Package;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Bootstrap sekali-jalan untuk tahun yang paketnya sudah terlanjur ada
 * tetapi plafon DPA-nya belum pernah dicatat.
 *
 * Membentuk satu baris anggaran per sel (tahun x sub kegiatan x rekening)
 * dengan revisi "APBD Murni" senilai jumlah pagu paket di sel tersebut,
 * sehingga monev tidak dimulai dari nol. Angka ini perkiraan awal — akan
 * dikoreksi saat DPA resmi diimpor atau diedit manual.
 *
 * Aman diulang: sel yang sudah punya baris anggaran dilewati.
 */
class SeedBudgetLines extends Command
{
    protected $signature = 'anggaran:seed-dpa
                            {--tahun= : Batasi pada satu tahun anggaran (mis. 2026)}
                            {--dry-run : Tampilkan rencana tanpa menyimpan}';

    protected $description = 'Bentuk baris anggaran DPA awal dari pagu paket RUP yang sudah ada';

    public function handle(): int
    {
        $tahun = $this->option('tahun');
        $dryRun = (bool) $this->option('dry-run');

        $sel = Package::query()
            ->whereNotNull('sub_activity_id')
            ->whereNotNull('account_id')
            ->when($tahun, fn ($q) => $q->whereHas('fiscalYear', fn ($f) => $f->where('tahun', $tahun)))
            ->selectRaw('fiscal_year_id, sub_activity_id, account_id, SUM(pagu) as total, COUNT(*) as jumlah')
            ->groupBy('fiscal_year_id', 'sub_activity_id', 'account_id')
            ->get();

        if ($sel->isEmpty()) {
            $this->warn('Tidak ada paket yang bisa dijadikan dasar.');

            return self::SUCCESS;
        }

        $dibuat = 0;
        $dilewati = 0;
        $totalPagu = 0.0;

        $jalankan = function () use ($sel, $dryRun, &$dibuat, &$dilewati, &$totalPagu) {
            foreach ($sel as $row) {
                $sudahAda = BudgetLine::where('fiscal_year_id', $row->fiscal_year_id)
                    ->where('sub_activity_id', $row->sub_activity_id)
                    ->where('account_id', $row->account_id)
                    ->exists();

                if ($sudahAda) {
                    $dilewati++;
                    continue;
                }

                $dibuat++;
                $totalPagu += (float) $row->total;

                if ($dryRun) {
                    continue;
                }

                $line = BudgetLine::create([
                    'fiscal_year_id' => $row->fiscal_year_id,
                    'sub_activity_id' => $row->sub_activity_id,
                    'account_id' => $row->account_id,
                    'pagu_efektif' => $row->total,
                    'keterangan' => 'Dibentuk otomatis dari pagu paket RUP; sesuaikan dengan DPA resmi.',
                ]);

                $line->revisions()->create([
                    'jenis' => BudgetRevision::JENIS_MURNI,
                    'urutan' => 1,
                    'pagu' => $row->total,
                    'keterangan' => 'Nilai awal dari penjumlahan ' . $row->jumlah . ' paket RUP.',
                ]);
            }
        };

        $dryRun ? $jalankan() : DB::transaction($jalankan);

        $this->newLine();
        $this->line(($dryRun ? '[SIMULASI] ' : '') . 'Baris anggaran dibentuk : ' . $dibuat);
        $this->line(($dryRun ? '[SIMULASI] ' : '') . 'Dilewati (sudah ada)    : ' . $dilewati);
        $this->line(($dryRun ? '[SIMULASI] ' : '') . 'Total pagu tercatat     : Rp ' . number_format($totalPagu, 0, ',', '.'));

        if ($dryRun) {
            $this->newLine();
            $this->comment('Tidak ada yang disimpan. Jalankan tanpa --dry-run untuk menerapkan.');
        }

        return self::SUCCESS;
    }
}
