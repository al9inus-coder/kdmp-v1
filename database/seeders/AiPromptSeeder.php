<?php

namespace Database\Seeders;

use App\Models\AiPrompt;
use Illuminate\Database\Seeder;

class AiPromptSeeder extends Seeder
{
    public function run(): void
    {
        $prompt = <<<'PROMPT'
Anda bertugas menyusun draf Spesifikasi Teknis / Kerangka Acuan Kerja (KAK) untuk paket pengadaan barang/jasa pemerintah daerah berikut.

DATA PAKET:
- SKPD: {SKPD}
- Nama Paket: {NAMA_PAKET}
- Program: {PROGRAM}
- Kegiatan: {KEGIATAN}
- Sub Kegiatan: {SUB_KEGIATAN}
- Jenis Pengadaan: {JENIS_PENGADAAN}

RINCIAN BARANG/JASA (JSON):
{ITEMS}

INSTRUKSI:
- Susun draf untuk bagian: latar_belakang, maksud (Maksud & Tujuan), target_sasaran (Target & Sasaran), dan uraian_pekerjaan.
- Gunakan bahasa formal pemerintahan yang jelas dan tidak bertele-tele.
- Dasarkan seluruh isi hanya pada data yang tersedia di atas. Jangan mengarang angka, merek, atau fakta yang tidak ada.
- uraian_pekerjaan harus mencakup lingkup pekerjaan yang konsisten dengan rincian barang/jasa yang tercantum.
PROMPT;

        AiPrompt::updateOrCreate(
            ['code' => 'technical_specification'],
            [
                'name' => 'Spesifikasi Teknis',
                'prompt' => $prompt,
                'is_active' => true,
            ]
        );
    }
}
