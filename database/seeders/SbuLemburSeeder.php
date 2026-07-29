<?php

namespace Database\Seeders;

use App\Models\SbuLembur;
use Illuminate\Database\Seeder;

class SbuLemburSeeder extends Seeder
{
    /**
     * Tarif lembur baku (Standar Biaya Umum).
     *
     * Berbeda dengan seeder SBU lain yang memakai truncate + insert, seeder ini
     * sengaja hanya MENAMBAH yang belum ada. Tarif lembur diisi dan disesuaikan
     * operator lewat halaman /admin/sbu-lemburs, dan label golongannya berupa
     * teks bebas — ada server yang menulis "IV", ada yang "Golongan IV".
     * Menghapus lalu menulis ulang berarti membuang penyesuaian yang sudah
     * dilakukan di produksi, jadi seeder ini aman dijalankan berulang.
     */
    public function run(): void
    {
        // 'token' bukan kolom tabel. Ia dipakai untuk memeriksa apakah suatu
        // golongan sudah punya tarif, memakai aturan pencocokan yang sama
        // persis dengan yang dipakai perhitungan lembur — sehingga label
        // apa pun yang sudah dipakai operator tetap dikenali.
        $baku = [
            ['jenis' => 'Uang Lembur', 'token' => 'IV', 'golongan' => 'Golongan IV', 'satuan' => 'OJ', 'besaran' => 36000],
            ['jenis' => 'Uang Lembur', 'token' => 'III', 'golongan' => 'Golongan III', 'satuan' => 'OJ', 'besaran' => 30000],
            ['jenis' => 'Uang Lembur', 'token' => 'II', 'golongan' => 'Golongan II', 'satuan' => 'OJ', 'besaran' => 24000],
            ['jenis' => 'Uang Lembur', 'token' => 'I', 'golongan' => 'Golongan I', 'satuan' => 'OJ', 'besaran' => 18000],
            ['jenis' => 'Uang Lembur', 'token' => 'P3K', 'golongan' => 'P3K Paruh Waktu', 'satuan' => 'OJ', 'besaran' => 15000],

            ['jenis' => 'Uang Makan Lembur', 'token' => 'IV', 'golongan' => 'Golongan IV', 'satuan' => 'OH', 'besaran' => 41000],
            ['jenis' => 'Uang Makan Lembur', 'token' => 'III', 'golongan' => 'Golongan III', 'satuan' => 'OH', 'besaran' => 37000],
            // Golongan II dan I bertarif sama, jadi cukup satu baris. Token I
            // diperiksa belakangan dan akan mengenali baris ini, bukan membuat
            // baris kembar.
            ['jenis' => 'Uang Makan Lembur', 'token' => 'II', 'golongan' => 'Golongan II dan Golongan I', 'satuan' => 'OH', 'besaran' => 35000],
            ['jenis' => 'Uang Makan Lembur', 'token' => 'I', 'golongan' => 'Golongan II dan Golongan I', 'satuan' => 'OH', 'besaran' => 35000],
            ['jenis' => 'Uang Makan Lembur', 'token' => 'P3K', 'golongan' => 'P3K Paruh Waktu', 'satuan' => 'OH', 'besaran' => 31000],
        ];

        $tarif = SbuLembur::all();
        $ditambah = 0;

        foreach ($baku as $baris) {
            $token = $baris['token'];
            unset($baris['token']);

            $sudahAda = $tarif->first(
                fn ($r) => $r->jenis === $baris['jenis'] && SbuLembur::labelMatchesToken($r->golongan, $token)
            );

            if ($sudahAda) {
                continue;
            }

            // Baris yang baru dibuat ikut dimasukkan ke koleksi supaya
            // pemeriksaan token berikutnya melihatnya juga.
            $tarif->push(SbuLembur::create($baris));
            $ditambah++;
        }

        $this->command?->info("SBU lembur: {$ditambah} tarif ditambahkan, {$tarif->count()} baris total.");
    }
}
