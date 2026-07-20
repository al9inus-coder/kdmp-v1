<?php

namespace App\Console\Commands;

use App\Models\ImportBatch;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Signature('import-batches:prune {--days=7 : Hapus riwayat import yang lebih tua dari sekian hari}')]
#[Description('Menghapus riwayat import batch beserta file excel-nya yang sudah lebih tua dari batas retensi. Paket yang sudah ter-import tidak ikut terhapus.')]
class PruneImportBatches extends Command
{
    public function handle(): int
    {
        $days = (int) $this->option('days');

        $batches = ImportBatch::query()
            ->where('created_at', '<', now()->subDays($days))
            ->get();

        foreach ($batches as $batch) {
            if ($batch->file_path) {
                Storage::disk('local')->delete($batch->file_path);
            }

            $batch->delete();
        }

        $this->info("Terhapus {$batches->count()} riwayat import batch (lebih tua dari {$days} hari).");

        return self::SUCCESS;
    }
}
