<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Program;
use App\Models\Skpd;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        $skpd = Skpd::first();

        Program::create([
            'skpd_id' => $skpd->id,
            'kode' => '1.02.01',
            'nama' => 'Pengelolaan Sampah',
            'tahun' => 2027,
            'pagu' => 2000000000
        ]);
    }
}