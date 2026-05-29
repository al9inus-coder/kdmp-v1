<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Program;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        Program::create([
            'kode' => '1.02.01',
            'nama' => 'Pengelolaan Sampah',
            'is_active' => true,
        ]);
    }
}