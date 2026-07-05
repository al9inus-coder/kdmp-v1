<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SbuUangHarianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['provinsi' => 'Aceh', 'satuan' => 'OH', 'luar_kota' => 360000, 'diklat' => 110000],
            ['provinsi' => 'Sumatera Utara', 'satuan' => 'OH', 'luar_kota' => 370000, 'diklat' => 110000],
            ['provinsi' => 'Riau', 'satuan' => 'OH', 'luar_kota' => 370000, 'diklat' => 110000],
            ['provinsi' => 'Kepulauan Riau', 'satuan' => 'OH', 'luar_kota' => 370000, 'diklat' => 110000],
            ['provinsi' => 'Jambi', 'satuan' => 'OH', 'luar_kota' => 370000, 'diklat' => 110000],
            ['provinsi' => 'Sumatera Barat', 'satuan' => 'OH', 'luar_kota' => 380000, 'diklat' => 110000],
            ['provinsi' => 'Sumatera Selatan', 'satuan' => 'OH', 'luar_kota' => 380000, 'diklat' => 110000],
            ['provinsi' => 'Lampung', 'satuan' => 'OH', 'luar_kota' => 380000, 'diklat' => 110000],
            ['provinsi' => 'Bengkulu', 'satuan' => 'OH', 'luar_kota' => 380000, 'diklat' => 110000],
            ['provinsi' => 'Bangka Belitung', 'satuan' => 'OH', 'luar_kota' => 410000, 'diklat' => 120000],
            ['provinsi' => 'Banten', 'satuan' => 'OH', 'luar_kota' => 370000, 'diklat' => 110000],
            ['provinsi' => 'Jawa Barat', 'satuan' => 'OH', 'luar_kota' => 430000, 'diklat' => 130000],
            ['provinsi' => 'D.K.I. Jakarta', 'satuan' => 'OH', 'luar_kota' => 530000, 'diklat' => 160000],
            ['provinsi' => 'Jawa Tengah', 'satuan' => 'OH', 'luar_kota' => 370000, 'diklat' => 110000],
            ['provinsi' => 'D.I. Yogyakarta', 'satuan' => 'OH', 'luar_kota' => 420000, 'diklat' => 130000],
            ['provinsi' => 'Jawa Timur', 'satuan' => 'OH', 'luar_kota' => 410000, 'diklat' => 120000],
            ['provinsi' => 'Bali', 'satuan' => 'OH', 'luar_kota' => 480000, 'diklat' => 140000],
            ['provinsi' => 'Nusa Tenggara Barat', 'satuan' => 'OH', 'luar_kota' => 440000, 'diklat' => 130000],
            ['provinsi' => 'Nusa Tenggara Timur', 'satuan' => 'OH', 'luar_kota' => 430000, 'diklat' => 130000],
            ['provinsi' => 'Kalimantan Barat', 'satuan' => 'OH', 'luar_kota' => 380000, 'diklat' => 110000],
            ['provinsi' => 'Kalimantan Tengah', 'satuan' => 'OH', 'luar_kota' => 360000, 'diklat' => 110000],
            ['provinsi' => 'Kalimantan Selatan', 'satuan' => 'OH', 'luar_kota' => 380000, 'diklat' => 110000],
            ['provinsi' => 'Kalimantan Timur', 'satuan' => 'OH', 'luar_kota' => 430000, 'diklat' => 130000],
            ['provinsi' => 'Kalimantan Utara', 'satuan' => 'OH', 'luar_kota' => 430000, 'diklat' => 130000],
            ['provinsi' => 'Sulawesi Utara', 'satuan' => 'OH', 'luar_kota' => 370000, 'diklat' => 110000],
            ['provinsi' => 'Gorontalo', 'satuan' => 'OH', 'luar_kota' => 370000, 'diklat' => 110000],
            ['provinsi' => 'Sulawesi Barat', 'satuan' => 'OH', 'luar_kota' => 410000, 'diklat' => 120000],
            ['provinsi' => 'Sulawesi Selatan', 'satuan' => 'OH', 'luar_kota' => 430000, 'diklat' => 130000],
            ['provinsi' => 'Sulawesi Tengah', 'satuan' => 'OH', 'luar_kota' => 370000, 'diklat' => 110000],
            ['provinsi' => 'Sulawesi Tenggara', 'satuan' => 'OH', 'luar_kota' => 380000, 'diklat' => 110000],
            ['provinsi' => 'Maluku', 'satuan' => 'OH', 'luar_kota' => 380000, 'diklat' => 110000],
            ['provinsi' => 'Maluku Utara', 'satuan' => 'OH', 'luar_kota' => 430000, 'diklat' => 130000],
            ['provinsi' => 'Papua', 'satuan' => 'OH', 'luar_kota' => 580000, 'diklat' => 170000],
            ['provinsi' => 'Papua Barat', 'satuan' => 'OH', 'luar_kota' => 480000, 'diklat' => 140000],
            ['provinsi' => 'Papua Barat Daya', 'satuan' => 'OH', 'luar_kota' => 480000, 'diklat' => 140000],
            ['provinsi' => 'Papua Tengah', 'satuan' => 'OH', 'luar_kota' => 580000, 'diklat' => 170000],
            ['provinsi' => 'Papua Selatan', 'satuan' => 'OH', 'luar_kota' => 580000, 'diklat' => 170000],
            ['provinsi' => 'Papua Pegunungan', 'satuan' => 'OH', 'luar_kota' => 580000, 'diklat' => 170000],
        ];
        
        \App\Models\SbuUangHarian::truncate();
        \App\Models\SbuUangHarian::insert($data);
    }
}
