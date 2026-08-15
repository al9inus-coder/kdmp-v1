<?php

namespace App\Console\Commands;

use App\Models\ProcurementPackage;
use App\Models\TarifPajak;
use App\Services\Pajak\PajakPengadaan;
use Illuminate\Console\Command;

/**
 * Memeriksa keutuhan pajak pembayaran yang sudah dibekukan.
 *
 * Nominal pajak tidak pernah dihitung ulang saat dicetak — ia disimpan apa
 * adanya supaya BAP yang sudah ditandatangani tidak bergeser. Konsekuensinya,
 * baris yang rusak tidak akan menimbulkan galat apa pun: dokumen tetap tercetak,
 * hanya angkanya salah. Perintah ini yang membuatnya terlihat.
 */
class PeriksaPajakPembayaran extends Command
{
    protected $signature = 'pajak:periksa {--rup= : batasi ke satu paket}';

    protected $description = 'Periksa keutuhan baris pajak pada pembayaran yang sudah dibekukan';

    public function handle(): int
    {
        $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');

        $paket = ProcurementPackage::with(['package.account', 'procurementProcess', 'payment.pajaks'])
            ->when($this->option('rup'), fn ($q, $rup) => $q->whereHas(
                'package', fn ($p) => $p->where('id_rup', $rup)))
            ->get()
            ->filter(fn ($pp) => $pp->payment && !is_null($pp->payment->nilai_kontrak_fix));

        if ($paket->isEmpty()) {
            $this->warn('Tidak ada pembayaran yang sudah dibekukan.');

            return self::SUCCESS;
        }

        $baris = [];
        $cacat = 0;

        foreach ($paket as $pp) {
            $bayar = $pp->payment;
            $nilai = (float) $bayar->nilai_kontrak_fix;
            $masalah = [];

            // 1. Nominal tersimpan harus sama dengan hitung ulang dari persen
            //    dan dasarnya. Kalau berbeda, salah satunya pernah ditulis
            //    tanpa lewat PajakPengadaan::bekukan().
            $ulang = PajakPengadaan::nilaiBaris($bayar->pajaks->map(fn ($p) => [
                'jenis' => $p->jenis,
                'kunci' => $p->kunci,
                'persen' => (float) $p->persen,
                'dasar' => $p->dasar,
            ])->all(), $nilai);

            foreach ($ulang as $i => $u) {
                $asli = $bayar->pajaks[$i];
                if (abs($u['nominal'] - (float) $asli->nominal) > 0.01) {
                    $masalah[] = "{$asli->jenis}: nominal {$rp($asli->nominal)} != {$rp($u['nominal'])}";
                }
                if (abs($u['nilaiDasar'] - (float) $asli->nilai_dasar) > 0.01) {
                    $masalah[] = "{$asli->jenis}: dasar {$rp($asli->nilai_dasar)} != {$rp($u['nilaiDasar'])}";
                }
            }

            // 2. Dasar yang merujuk pajak yang tidak ada barisnya akan diam-diam
            //    jatuh ke nilai kontrak penuh — sah sebagai pilihan sadar, tapi
            //    lebih sering tanda baris pajaknya terhapus tanpa sengaja.
            $ada = $bayar->pajaks->pluck('jenis')->all();
            foreach ($bayar->pajaks as $p) {
                $butuh = match ($p->dasar) {
                    PajakPengadaan::DASAR_SETELAH_PPN => TarifPajak::PPN,
                    PajakPengadaan::DASAR_SETELAH_PBJT => TarifPajak::PAJAK_RESTORAN,
                    default => null,
                };
                if ($butuh && !in_array($butuh, $ada, true)) {
                    $masalah[] = "{$p->jenis}: dasar '{$p->dasar}' tetapi baris {$butuh} tidak ada";
                }
            }

            if ($masalah) {
                $cacat++;
            }

            $baris[] = [
                $pp->package?->id_rup ?? $pp->id,
                $bayar->pajaks->count(),
                $rp($nilai),
                $masalah ? '✗ ' . implode('; ', $masalah) : '✓ utuh',
            ];
        }

        $this->table(['ID RUP', 'BARIS', 'KONTRAK BEKU', 'PEMERIKSAAN'], $baris);

        if ($cacat) {
            $this->error("{$cacat} pembayaran cacat dari {$paket->count()} yang diperiksa.");

            return self::FAILURE;
        }

        $this->info("{$paket->count()} pembayaran diperiksa, seluruhnya utuh.");

        return self::SUCCESS;
    }
}
