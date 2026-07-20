<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SbuTiketPesawatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['Banda Aceh/Nanggroe Aceh Darussalam', 9990000, 5840000],
            ['Medan/Sumatera Utara', 9733000, 5230000],
            ['Padang/Sumatera Barat', 8193000, 4460000],
            ['Batam/Kepulauan Riau', 7594000, 4396000],
            ['Pekanbaru/Riau', 8247000, 4514000],
            ['Jambi/Jambi', 6878000, 4011000],
            ['Palembang/Sumatera Selatan', 6685000, 3840000],
            ['Bengkulu/Bengkulu', 6685000, 3840000],
            ['Bandar Lampung/Lampung', 5380000, 3220000],
            ['Pangkal Pinang/Kepulauan Bangka Belitung', 6279000, 3733000],
            ['Tanjung Pinang/Kepulauan Riau', 8247000, 4514000],
            ['Jakarta/D.K.I. Jakarta', 4353000, 2781000],
            ['Bandung/Jawa Barat', 4353000, 2781000],
            ['Semarang/Jawa Tengah', 6685000, 3765000],
            ['Yogyakarta/D.I. Yogyakarta', 6910000, 3840000],
            ['Solo/Jawa Tengah', 6685000, 3904000],
            ['Surabaya/Jawa Timur', 8140000, 4204000],
            ['Denpasar/Bali', 7990000, 4738000],
            ['Mataram/Nusa Tenggara Barat', 8001000, 4706000],
            ['Kupang/Nusa Tenggara Timur', 8001000, 4706000],
            ['Palangkaraya/Kalimantan Tengah', 9337000, 5765000],
            ['Banjarmasin/Kalimantan Selatan', 9605000, 5776000],
            ['Samarinda/Kalimantan Timur', 11765000, 6578000],
            ['Tanjung Selor/Tarakan/Kalimantan Utara', 11765000, 6578000],
            ['Manado/Sulawesi Utara', 12953000, 6396000],
            ['Palu/Sulawesi Tengah', 12953000, 6396000],
            ['Makassar/Sulawesi Selatan', 9915000, 5241000],
            ['Kendari/Sulawesi Tenggara', 12953000, 6396000],
            ['Gorontalo/Gorontalo', 12953000, 6396000],
            ['Mamuju/Sulawesi Barat', 9915000, 5241000],
            ['Ambon/Maluku', 12953000, 6396000],
            ['Sofifi/Maluku Utara', 12953000, 6396000],
            ['Jayapura/Papua', 16322000, 9177000],
            ['Manokwari/Papua Barat', 16322000, 9177000],
            ['Timika/Papua Tengah', 16322000, 9177000],
            ['Biak/Papua', 16322000, 9177000],
            ['Ketapang/Ketapang', 0, 1500000],
            ['Sintang/Sintang', 0, 1500000],
            ['Nanga Pinoh/Melawi', 0, 1500000],
            ['Putussibau/Kapuas Hulu', 0, 2000000],
        ];

        foreach ($data as $row) {
            \App\Models\SbuTiketPesawat::updateOrCreate(
                ['tujuan' => $row[0]],
                [
                    'satuan' => 'PP',
                    'bisnis' => $row[1],
                    'ekonomi' => $row[2],
                ]
            );
        }
    }
}
