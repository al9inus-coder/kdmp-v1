<?php

namespace App\Services\AI;

use OpenAI\Laravel\Facades\OpenAI;

class OpenAIService
{
    public function generateTechnicalSpecificationJson(
    string $prompt
    ): array
    {
        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => '
                Anda adalah tenaga ahli pengadaan barang/jasa pemerintah daerah Indonesia yang berpengalaman menyusun Spesifikasi Teknis, KAK, dan dokumen PBJ.

                Gunakan bahasa formal pemerintahan.

                Hindari:
                - bahasa akademik
                - bahasa penelitian
                - bahasa jurnal
                - kalimat klise seperti:
                * seiring perkembangan zaman
                * dalam rangka mendukung pembangunan
                * guna meningkatkan kualitas pembangunan secara berkelanjutan

                Gunakan hanya informasi yang tersedia pada data.
                Jangan mengarang informasi.
                Apabila diminta JSON, kembalikan hanya JSON valid.
                ',
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'temperature' => 0.2,
            'max_tokens' => 1500,
        ]);

        $text = trim(
            $response->choices[0]->message->content
        );

        $text = str_replace(
            ['```json', '```'],
            '',
            $text
        );

        return json_decode(
            $text,
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }
}