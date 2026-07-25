<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sambungan ke AI Service (aplikasi ai-kdmp)
    |--------------------------------------------------------------------------
    |
    | Seluruh panggilan ke AI Service dilakukan server-to-server lewat
    | AiProxyController. Kunci internal di bawah TIDAK BOLEH ikut dirender ke
    | halaman atau dipakai dari JavaScript — browser cukup memanggil KDMP
    | dengan session + CSRF seperti permintaan biasa.
    |
    */

    'base_url' => rtrim((string) env('AI_SERVICE_URL', 'http://127.0.0.1:8000'), '/'),

    'secret' => env('AI_SERVICE_SECRET'),

    'timeout' => (int) env('AI_SERVICE_TIMEOUT', 30),

];
