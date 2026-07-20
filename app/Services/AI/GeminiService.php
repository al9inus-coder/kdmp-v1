<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;

class GeminiService
{
    public function test(): string
    {
        $apiKey = config('services.gemini.api_key');

        $response = Http::post(
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey,
            [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => 'Balas tepat dengan kalimat: Halo KDMP'
                            ]
                        ]
                    ]
                ]
            ]
        );

        if (! $response->successful()) {
            throw new \Exception(
                $response->body()
            );
        }

        return data_get(
            $response->json(),
            'candidates.0.content.parts.0.text',
            'Tidak ada respons'
        );
    }

    public function generateTechnicalSpecification(
        string $prompt
    ): string
    {
        $apiKey = config('services.gemini.api_key');

        $response = Http::post(
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey,
            [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => $prompt
                            ]
                        ]
                    ]
                ]
            ]
        );

        if (! $response->successful()) {
            throw new \Exception(
                $response->body()
            );
        }

        return data_get(
            $response->json(),
            'candidates.0.content.parts.0.text',
            ''
        );
    }

    public function generateTechnicalSpecificationJson(
    string $prompt
): array
{
    $apiKey = config('services.gemini.api_key');

    $response = Http::post(
        'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey,
        [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $prompt
                        ]
                    ]
                ]
            ]
        ]
    );

    if (! $response->successful()) {
        throw new \Exception(
            $response->body()
        );
    }

    $text = data_get(
        $response->json(),
        'candidates.0.content.parts.0.text',
        ''
    );

    $text = str_replace(
        ['```json', '```'],
        '',
        trim($text)
    );

    return json_decode(
        $text,
        true,
        512,
        JSON_THROW_ON_ERROR
    );
}
}