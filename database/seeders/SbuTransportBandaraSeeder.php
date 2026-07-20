<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SbuTransportBandaraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['provinsi' => 'Aceh', 'di_tempat_tujuan' => 246000],
            ['provinsi' => 'Sumatera Utara', 'di_tempat_tujuan' => 464000],
            ['provinsi' => 'Riau', 'di_tempat_tujuan' => 188000],
            ['provinsi' => 'Kepulauan Riau', 'di_tempat_tujuan' => 274000],
            ['provinsi' => 'Jambi', 'di_tempat_tujuan' => 294000],
            ['provinsi' => 'Sumatera Barat', 'di_tempat_tujuan' => 380000],
            ['provinsi' => 'Sumatera Selatan', 'di_tempat_tujuan' => 256000],
            ['provinsi' => 'Lampung', 'di_tempat_tujuan' => 334000],
            ['provinsi' => 'Bengkulu', 'di_tempat_tujuan' => 218000],
            ['provinsi' => 'Bangka Belitung', 'di_tempat_tujuan' => 180000],
            ['provinsi' => 'Banten', 'di_tempat_tujuan' => 892000],
            ['provinsi' => 'Jawa Barat', 'di_tempat_tujuan' => 332000],
            ['provinsi' => 'D.K.I. Jakarta', 'di_tempat_tujuan' => 572000],
            ['provinsi' => 'Jawa Tengah', 'di_tempat_tujuan' => 150000],
            ['provinsi' => 'D.I. Yogyakarta', 'di_tempat_tujuan' => 236000],
            ['provinsi' => 'Jawa Timur', 'di_tempat_tujuan' => 388000],
            ['provinsi' => 'Bali', 'di_tempat_tujuan' => 318000],
            ['provinsi' => 'Nusa Tenggara Barat', 'di_tempat_tujuan' => 462000],
            ['provinsi' => 'Nusa Tenggara Timur', 'di_tempat_tujuan' => 216000],
            ['provinsi' => 'Kalimantan Barat', 'di_tempat_tujuan' => 270000],
            ['provinsi' => 'Kalimantan Tengah', 'di_tempat_tujuan' => 222000],
            ['provinsi' => 'Kalimantan Selatan', 'di_tempat_tujuan' => 300000],
            ['provinsi' => 'Kalimantan Timur', 'di_tempat_tujuan' => 900000],
            ['provinsi' => 'Kalimantan Utara', 'di_tempat_tujuan' => 204000],
            ['provinsi' => 'Sulawesi Utara', 'di_tempat_tujuan' => 276000],
            ['provinsi' => 'Gorontalo', 'di_tempat_tujuan' => 480000],
            ['provinsi' => 'Sulawesi Barat', 'di_tempat_tujuan' => 626000],
            ['provinsi' => 'Sulawesi Selatan', 'di_tempat_tujuan' => 290000],
            ['provinsi' => 'Sulawesi Tengah', 'di_tempat_tujuan' => 330000],
            ['provinsi' => 'Sulawesi Tenggara', 'di_tempat_tujuan' => 342000],
            ['provinsi' => 'Maluku', 'di_tempat_tujuan' => 480000],
            ['provinsi' => 'Maluku Utara', 'di_tempat_tujuan' => 430000],
            ['provinsi' => 'Papua', 'di_tempat_tujuan' => 862000],
            ['provinsi' => 'Papua Barat', 'di_tempat_tujuan' => 364000],
            ['provinsi' => 'Papua Barat Daya', 'di_tempat_tujuan' => 862000],
            ['provinsi' => 'Papua Tengah', 'di_tempat_tujuan' => 862000],
            ['provinsi' => 'Papua Selatan', 'di_tempat_tujuan' => 862000],
            ['provinsi' => 'Papua Pegunungan', 'di_tempat_tujuan' => 862000],
            ['provinsi' => 'Kab. Kapuas Hulu', 'di_tempat_tujuan' => 270000],
            ['provinsi' => 'Kab. Sintang', 'di_tempat_tujuan' => 270000],
            ['provinsi' => 'Kab. Ketapang', 'di_tempat_tujuan' => 270000],
            ['provinsi' => 'Kab. Melawi', 'di_tempat_tujuan' => 270000],
            ['provinsi' => 'Kota Singkawang', 'di_tempat_tujuan' => 270000],
        ];

        // Apply defaults and insert
        $insertData = array_map(function($item) {
            $item['satuan'] = 'PP';
            $item['di_tempat_kedudukan'] = 540000;
            $item['created_at'] = now();
            $item['updated_at'] = now();
            return $item;
        }, $data);

        \App\Models\SbuTransportBandara::truncate();
        \App\Models\SbuTransportBandara::insert($insertData);
    }
}
