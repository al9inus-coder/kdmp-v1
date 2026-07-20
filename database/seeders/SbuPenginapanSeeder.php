<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SbuPenginapanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['Aceh', 2115600, 1262400, 770000],
            ['Sumatera Utara', 1317000, 950400, 699000],
            ['Riau', 1871400, 1320000, 852000],
            ['Kepulauan Riau', 1390800, 1037600, 792000],
            ['Jambi', 2461200, 980000, 580000],
            ['Sumatera Barat', 1999200, 1082400, 701000],
            ['Sumatera Selatan', 1849800, 1572800, 861000],
            ['Lampung', 1492800, 1231200, 580000],
            ['Bengkulu', 976800, 1236800, 692000],
            ['Bangka Belitung', 1702800, 1565600, 676000],
            ['Banten', 1423800, 1040800, 724000],
            ['Jawa Barat', 1653000, 1038400, 686000],
            ['D.K.I. Jakarta', 1237800, 793600, 730000],
            ['Jawa Tengah', 1198800, 960800, 810000],
            ['D.I. Yogyakarta', 1617000, 1196000, 845000],
            ['Jawa Timur', 1204200, 922400, 814000],
            ['Bali', 1459800, 1348000, 1138000],
            ['Nusa Tenggara Barat', 1588800, 1134400, 907000],
            ['Nusa Tenggara Timur', 1279800, 1084000, 688000],
            ['Kalimantan Barat', 1153800, 900000, 538000],
            ['Kalimantan Tengah', 2034600, 928000, 659000],
            ['Kalimantan Selatan', 1989600, 1200000, 697000],
            ['Kalimantan Timur', 1312800, 1205600, 804000],
            ['Kalimantan Utara', 1641000, 1205600, 904000],
            ['Sulawesi Utara', 1374000, 1016000, 978000],
            ['Gorontalo', 1864200, 1284800, 955000],
            ['Sulawesi Barat', 1858800, 1075200, 704000],
            ['Sulawesi Selatan', 1162800, 1138400, 745000],
            ['Sulawesi Tengah', 1216200, 1343200, 951000],
            ['Sulawesi Tenggara', 1544400, 1037600, 786000],
            ['Maluku', 1944000, 847200, 667000],
            ['Maluku Utara', 2305800, 928000, 654000],
            ['Papua', 1990800, 2016800, 1038000],
            ['Papua Barat', 2004600, 1644800, 967000],
            ['Papua Barat Daya', 2004600, 1644800, 967000],
            ['Papua Tengah', 1990800, 2016800, 1038000],
            ['Papua Selatan', 2926200, 2964800, 1526000],
            ['Papua Pegunungan', 2946600, 2984800, 1536000],
        ];

        foreach ($data as $row) {
            \App\Models\SbuPenginapan::updateOrCreate(
                ['provinsi' => $row[0]],
                [
                    'satuan' => 'OH',
                    'eselon_ii' => $row[1],
                    'eselon_iii' => $row[2],
                    'eselon_iv' => $row[3],
                ]
            );
        }
    }
}
