<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SbuTransportRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['tempat_kedudukan' => 'Bengkayang', 'tempat_tujuan' => 'Sungai Raya', 'satuan' => 'PP', 'biaya_mobil' => 400000, 'biaya_motor' => 100000, 'kategori' => 'dalam_daerah'],
            ['tempat_kedudukan' => 'Bengkayang', 'tempat_tujuan' => 'Sungai Raya Kepulauan', 'satuan' => 'PP', 'biaya_mobil' => 330000, 'biaya_motor' => 80000, 'kategori' => 'dalam_daerah'],
            ['tempat_kedudukan' => 'Bengkayang', 'tempat_tujuan' => 'Capkala', 'satuan' => 'PP', 'biaya_mobil' => 220000, 'biaya_motor' => 60000, 'kategori' => 'dalam_daerah'],
            ['tempat_kedudukan' => 'Bengkayang', 'tempat_tujuan' => 'Monterado', 'satuan' => 'PP', 'biaya_mobil' => 170000, 'biaya_motor' => 50000, 'kategori' => 'dalam_daerah'],
            ['tempat_kedudukan' => 'Bengkayang', 'tempat_tujuan' => 'Samalantan', 'satuan' => 'PP', 'biaya_mobil' => 140000, 'biaya_motor' => 50000, 'kategori' => 'dalam_daerah'],
            ['tempat_kedudukan' => 'Bengkayang', 'tempat_tujuan' => 'Lembah Bawang', 'satuan' => 'PP', 'biaya_mobil' => 200000, 'biaya_motor' => 50000, 'kategori' => 'dalam_daerah'],
            ['tempat_kedudukan' => 'Bengkayang', 'tempat_tujuan' => 'Sungai Betung', 'satuan' => 'PP', 'biaya_mobil' => 100000, 'biaya_motor' => 50000, 'kategori' => 'dalam_daerah'],
            ['tempat_kedudukan' => 'Bengkayang', 'tempat_tujuan' => 'Teriak', 'satuan' => 'PP', 'biaya_mobil' => 100000, 'biaya_motor' => 50000, 'kategori' => 'dalam_daerah'],
            ['tempat_kedudukan' => 'Bengkayang', 'tempat_tujuan' => 'Suti Semarang', 'satuan' => 'PP', 'biaya_mobil' => 200000, 'biaya_motor' => 100000, 'kategori' => 'dalam_daerah'],
            ['tempat_kedudukan' => 'Bengkayang', 'tempat_tujuan' => 'Lumar', 'satuan' => 'PP', 'biaya_mobil' => 100000, 'biaya_motor' => 50000, 'kategori' => 'dalam_daerah'],
            ['tempat_kedudukan' => 'Bengkayang', 'tempat_tujuan' => 'Ledo', 'satuan' => 'PP', 'biaya_mobil' => 130000, 'biaya_motor' => 50000, 'kategori' => 'dalam_daerah'],
            ['tempat_kedudukan' => 'Bengkayang', 'tempat_tujuan' => 'Sanggau Ledo', 'satuan' => 'PP', 'biaya_mobil' => 190000, 'biaya_motor' => 50000, 'kategori' => 'dalam_daerah'],
            ['tempat_kedudukan' => 'Bengkayang', 'tempat_tujuan' => 'Tujuh Belas', 'satuan' => 'PP', 'biaya_mobil' => 210000, 'biaya_motor' => 60000, 'kategori' => 'dalam_daerah'],
            ['tempat_kedudukan' => 'Bengkayang', 'tempat_tujuan' => 'Seluas', 'satuan' => 'PP', 'biaya_mobil' => 260000, 'biaya_motor' => 70000, 'kategori' => 'dalam_daerah'],
            ['tempat_kedudukan' => 'Bengkayang', 'tempat_tujuan' => 'Jagoi Babang', 'satuan' => 'PP', 'biaya_mobil' => 310000, 'biaya_motor' => 80000, 'kategori' => 'dalam_daerah'],
            ['tempat_kedudukan' => 'Bengkayang', 'tempat_tujuan' => 'Siding', 'satuan' => 'PP', 'biaya_mobil' => 360000, 'biaya_motor' => 90000, 'kategori' => 'dalam_daerah'],
            
            // LUAR DAERAH (Kalimantan Barat)
            ['tempat_kedudukan' => 'Bengkayang', 'tempat_tujuan' => 'Pontianak', 'satuan' => 'PP', 'biaya_mobil' => 540000, 'biaya_motor' => 130000, 'kategori' => 'luar_daerah'],
            ['tempat_kedudukan' => 'Bengkayang', 'tempat_tujuan' => 'Kab. Kapuas Hulu', 'satuan' => 'PP', 'biaya_mobil' => 1906000, 'biaya_motor' => 460000, 'kategori' => 'luar_daerah'],
            ['tempat_kedudukan' => 'Bengkayang', 'tempat_tujuan' => 'Kab. Kayong Utara', 'satuan' => 'PP', 'biaya_mobil' => 1454000, 'biaya_motor' => 350000, 'kategori' => 'luar_daerah'],
            ['tempat_kedudukan' => 'Bengkayang', 'tempat_tujuan' => 'Kab. Ketapang', 'satuan' => 'PP', 'biaya_mobil' => 1855000, 'biaya_motor' => 450000, 'kategori' => 'luar_daerah'],
            ['tempat_kedudukan' => 'Bengkayang', 'tempat_tujuan' => 'Kab. Kubu Raya', 'satuan' => 'PP', 'biaya_mobil' => 540000, 'biaya_motor' => 130000, 'kategori' => 'luar_daerah'],
            ['tempat_kedudukan' => 'Bengkayang', 'tempat_tujuan' => 'Kab. Landak', 'satuan' => 'PP', 'biaya_mobil' => 356000, 'biaya_motor' => 90000, 'kategori' => 'luar_daerah'],
            ['tempat_kedudukan' => 'Bengkayang', 'tempat_tujuan' => 'Kab. Melawi', 'satuan' => 'PP', 'biaya_mobil' => 1205000, 'biaya_motor' => 290000, 'kategori' => 'luar_daerah'],
            ['tempat_kedudukan' => 'Bengkayang', 'tempat_tujuan' => 'Kab. Mempawah', 'satuan' => 'PP', 'biaya_mobil' => 420000, 'biaya_motor' => 110000, 'kategori' => 'luar_daerah'],
            ['tempat_kedudukan' => 'Bengkayang', 'tempat_tujuan' => 'Kab. Sambas', 'satuan' => 'PP', 'biaya_mobil' => 298000, 'biaya_motor' => 80000, 'kategori' => 'luar_daerah'],
            ['tempat_kedudukan' => 'Bengkayang', 'tempat_tujuan' => 'Kab. Sanggau', 'satuan' => 'PP', 'biaya_mobil' => 640000, 'biaya_motor' => 160000, 'kategori' => 'luar_daerah'],
            ['tempat_kedudukan' => 'Bengkayang', 'tempat_tujuan' => 'Kab. Sekadau', 'satuan' => 'PP', 'biaya_mobil' => 821000, 'biaya_motor' => 200000, 'kategori' => 'luar_daerah'],
            ['tempat_kedudukan' => 'Bengkayang', 'tempat_tujuan' => 'Kab. Sintang', 'satuan' => 'PP', 'biaya_mobil' => 1057000, 'biaya_motor' => 260000, 'kategori' => 'luar_daerah'],
            ['tempat_kedudukan' => 'Bengkayang', 'tempat_tujuan' => 'Kota Singkawang', 'satuan' => 'PP', 'biaya_mobil' => 256000, 'biaya_motor' => 70000, 'kategori' => 'luar_daerah'],
        ];
        
        \App\Models\SbuTransportRate::truncate();
        \App\Models\SbuTransportRate::insert($data);
    }
}
