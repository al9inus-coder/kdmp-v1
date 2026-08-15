<?php

namespace Database\Seeders;

use App\Models\SbuLembur;
use App\Models\TarifPajak;
use App\Services\Pajak\PajakPengadaan;
use Illuminate\Database\Seeder;

/**
 * Tarif pajak baku.
 *
 * Seperti SbuLemburSeeder, hanya MENAMBAH yang belum ada — tarif disesuaikan
 * operator lewat /admin/pajak mengikuti regulasi, dan menimpanya berarti
 * membuang penyesuaian itu.
 */
class TarifPajakSeeder extends Seeder
{
    public function run(): void
    {
        $baku = [
            // PPh 21 per golongan — berlaku untuk lembur, dan nanti honorarium
            // serta penghasilan lain. Golongan I dan II berbagi satu baris; token
            // kedua akan mengenali baris yang sama dan melewatinya.
            ['jenis' => TarifPajak::PPH21, 'token' => 'I', 'kunci' => 'Golongan I dan Golongan II', 'persen' => 0, 'dasar' => PajakPengadaan::DASAR_BRUTO, 'keterangan' => 'Bebas potongan pajak'],
            ['jenis' => TarifPajak::PPH21, 'token' => 'II', 'kunci' => 'Golongan I dan Golongan II', 'persen' => 0, 'dasar' => PajakPengadaan::DASAR_BRUTO, 'keterangan' => 'Bebas potongan pajak'],
            ['jenis' => TarifPajak::PPH21, 'token' => 'III', 'kunci' => 'Golongan III', 'persen' => 5, 'dasar' => PajakPengadaan::DASAR_BRUTO, 'keterangan' => 'Dipotong langsung 5%'],
            ['jenis' => TarifPajak::PPH21, 'token' => 'IV', 'kunci' => 'Golongan IV', 'persen' => 15, 'dasar' => PajakPengadaan::DASAR_BRUTO, 'keterangan' => 'Dipotong langsung 15%'],
            ['jenis' => TarifPajak::PPH21, 'token' => 'P3K', 'kunci' => 'P3K Paruh Waktu', 'persen' => 5, 'dasar' => PajakPengadaan::DASAR_BRUTO, 'keterangan' => 'PPPK dipotong 5%'],

            // Pajak pengadaan yang berlaku menyeluruh.
            ['jenis' => TarifPajak::PPN, 'kunci' => null, 'persen' => 11, 'dasar' => PajakPengadaan::DASAR_SETELAH_PPN, 'keterangan' => 'Nilai kontrak sudah termasuk PPN'],
            ['jenis' => TarifPajak::PPH22_BARANG, 'kunci' => null, 'persen' => 1.5, 'dasar' => PajakPengadaan::DASAR_SETELAH_PPN, 'keterangan' => 'Pengadaan barang'],
            ['jenis' => TarifPajak::PPH23_JASA, 'kunci' => null, 'persen' => 2, 'dasar' => PajakPengadaan::DASAR_SETELAH_PPN, 'keterangan' => 'Pengadaan jasa'],
            ['jenis' => TarifPajak::PAJAK_RESTORAN, 'kunci' => null, 'persen' => 10, 'dasar' => PajakPengadaan::DASAR_SETELAH_PBJT, 'keterangan' => 'Menggantikan PPN; nilai kontrak sudah termasuk pajak restoran'],

            // PPh Final Pasal 4(2) konstruksi — pondasi untuk pengadaan
            // konstruksi yang belum ada. Angka bawaan ini titik awal dan WAJIB
            // dikonfirmasi ke bendahara sebelum dipakai.
            ['jenis' => TarifPajak::PPH4_2_KONSTRUKSI, 'kunci' => 'Kualifikasi Kecil', 'persen' => 1.75, 'dasar' => PajakPengadaan::DASAR_SETELAH_PPN, 'keterangan' => 'Perlu dikonfirmasi ke bendahara'],
            ['jenis' => TarifPajak::PPH4_2_KONSTRUKSI, 'kunci' => 'Kualifikasi Menengah/Besar', 'persen' => 2.65, 'dasar' => PajakPengadaan::DASAR_SETELAH_PPN, 'keterangan' => 'Perlu dikonfirmasi ke bendahara'],
            ['jenis' => TarifPajak::PPH4_2_KONSTRUKSI, 'kunci' => 'Tanpa Kualifikasi', 'persen' => 4, 'dasar' => PajakPengadaan::DASAR_SETELAH_PPN, 'keterangan' => 'Perlu dikonfirmasi ke bendahara'],
            ['jenis' => TarifPajak::PPH4_2_KONSTRUKSI, 'kunci' => 'Perencanaan/Pengawasan', 'persen' => 3.5, 'dasar' => PajakPengadaan::DASAR_SETELAH_PPN, 'keterangan' => 'Perlu dikonfirmasi ke bendahara'],
        ];

        $tarif = TarifPajak::all();
        $ditambah = 0;

        foreach ($baku as $baris) {
            $token = $baris['token'] ?? null;
            unset($baris['token']);

            $sudahAda = $token
                ? $tarif->first(fn ($t) => $t->jenis === $baris['jenis'] && SbuLembur::labelMatchesToken($t->kunci, $token))
                : $tarif->first(fn ($t) => $t->jenis === $baris['jenis'] && $t->kunci === $baris['kunci']);

            if ($sudahAda) {
                continue;
            }

            $tarif->push(TarifPajak::create($baris));
            $ditambah++;
        }

        $this->command?->info("Tarif pajak: {$ditambah} ditambahkan, {$tarif->count()} baris total.");
    }
}
