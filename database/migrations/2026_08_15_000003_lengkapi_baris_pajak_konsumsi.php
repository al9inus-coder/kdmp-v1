<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Melengkapi baris pajak konsumsi yang hilang saat pemindahan.
 *
 * Migrasi 000002 mengambil persen dari tarif_pajaks bila kolom _fix masih
 * kosong. Di server produksi tabel tarif itu baru diisi seeder SESUDAH migrasi
 * jalan, jadi saat itu ia kosong dan baris PPN/PBJT tidak pernah dibuat —
 * setiap pembayaran hanya punya baris PPh, dan dasarnya menggantung ke nilai
 * kontrak penuh. Gejalanya tidak berupa galat: BAP tetap tercetak, hanya
 * kehilangan baris PPN dan memotong PPh terlalu besar.
 *
 * Perbaikan ini menambahkan baris yang hilang lalu menghitung ulang rupiah
 * seluruh baris pembayaran itu. Pembayaran yang sudah lengkap tidak disentuh.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Angka yang dulu tertanam di cetakan BAP sebelum Master Pajak ada.
        // Diperlukan sebab deploy.sh menjalankan migrasi SEBELUM seeder, jadi
        // tabel tarif bisa saja masih kosong saat perbaikan ini jalan — dan
        // melewatkannya berarti membiarkan BAP tanpa baris PPN.
        $bawaan = [
            'ppn' => 11.0,
            'pajak_restoran' => 10.0,
            'pph22_barang' => 1.5,
            'pph23_jasa' => 2.0,
        ];

        $tarif = DB::table('tarif_pajaks')->where('aktif', true)->get();

        $persenTarif = function (string $jenis) use ($tarif, $bawaan) {
            foreach ($tarif as $t) {
                if ($t->jenis === $jenis) {
                    return (float) $t->persen;
                }
            }

            return $bawaan[$jenis] ?? null;
        };

        $bayar = DB::table('procurement_payments')
            ->whereNotNull('nilai_kontrak_fix')
            ->get(['id', 'skema_pajak', 'nilai_kontrak_fix']);

        $diperbaiki = 0;
        $dilewati = 0;

        foreach ($bayar as $r) {
            $baris = DB::table('procurement_payment_pajaks')
                ->where('procurement_payment_id', $r->id)
                ->orderBy('urutan')
                ->get();

            if ($baris->isEmpty()) {
                // Tidak ada pajak dipungut — keadaan yang sah, jangan diisi.
                continue;
            }

            $restoran = $r->skema_pajak === 'restoran';
            $jenisKonsumsi = $restoran ? 'pajak_restoran' : 'ppn';
            $dasarKonsumsi = $restoran ? 'setelah_pbjt' : 'setelah_ppn';

            $adaKonsumsi = $baris->contains(fn ($b) => in_array($b->jenis, ['ppn', 'pajak_restoran'], true));

            // Baris konsumsi menggantung: ada yang memakai dasar "setelah X"
            // sementara baris X-nya tidak ada.
            $menggantung = !$adaKonsumsi
                && $baris->contains(fn ($b) => in_array($b->dasar, ['setelah_ppn', 'setelah_pbjt'], true));

            // Persen nol pada PPh pengadaan adalah sisa migrasi yang berjalan
            // saat tabel tarif masih kosong. Tidak dipungut dinyatakan dengan
            // MENGHAPUS barisnya, jadi baris 0% memang tidak punya arti lain.
            $nolPalsu = $baris->filter(fn ($b) => (float) $b->persen == 0.0
                && in_array($b->jenis, ['pph22_barang', 'pph23_jasa', 'ppn', 'pajak_restoran'], true));

            if (!$menggantung && $nolPalsu->isEmpty()) {
                $dilewati++;
                continue;
            }

            foreach ($nolPalsu as $b) {
                $p = $persenTarif($b->jenis);
                if (!is_null($p) && $p > 0) {
                    DB::table('procurement_payment_pajaks')->where('id', $b->id)
                        ->update(['persen' => $p, 'updated_at' => now()]);
                }
            }

            $persen = $persenTarif($jenisKonsumsi);

            if ($menggantung && !is_null($persen)) {
                // Baris konsumsi selalu paling depan; sisanya digeser.
                DB::table('procurement_payment_pajaks')
                    ->where('procurement_payment_id', $r->id)
                    ->increment('urutan');

                DB::table('procurement_payment_pajaks')->insert([
                    'procurement_payment_id' => $r->id,
                    'jenis' => $jenisKonsumsi,
                    'kunci' => null,
                    'persen' => $persen,
                    'dasar' => $dasarKonsumsi,
                    'nilai_dasar' => 0,
                    'nominal' => 0,
                    'urutan' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Hitung ulang rupiah seluruh baris pembayaran ini. Dasarnya sama
            // untuk semua: nilai kontrak dibagi (1 + persen konsumsi / 100).
            $nilai = (float) $r->nilai_kontrak_fix;
            $persenKonsumsi = (float) (DB::table('procurement_payment_pajaks')
                ->where('procurement_payment_id', $r->id)
                ->whereIn('jenis', ['ppn', 'pajak_restoran'])
                ->value('persen') ?? 0);
            $dasar = $persenKonsumsi > 0 ? $nilai / (1 + $persenKonsumsi / 100) : $nilai;

            foreach (DB::table('procurement_payment_pajaks')
                ->where('procurement_payment_id', $r->id)->get() as $b) {
                $pakaiDasar = in_array($b->dasar, ['setelah_ppn', 'setelah_pbjt'], true) ? $dasar : $nilai;

                DB::table('procurement_payment_pajaks')->where('id', $b->id)->update([
                    'nilai_dasar' => round($pakaiDasar, 2),
                    'nominal' => round($pakaiDasar * (float) $b->persen / 100, 2),
                    'updated_at' => now(),
                ]);
            }

            $diperbaiki++;
        }

        echo "  {$diperbaiki} pembayaran dilengkapi, {$dilewati} sudah benar\n";
    }

    public function down(): void
    {
        // Tidak dibalik: yang dihapus adalah baris pajak yang seharusnya ada
        // sejak awal, dan membuangnya kembali hanya memulihkan keadaan cacat.
    }
};
