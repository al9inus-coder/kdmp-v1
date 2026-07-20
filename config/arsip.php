<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Link Google Drive per Tahun Anggaran
    |--------------------------------------------------------------------------
    |
    | Di halaman Arsip, tiap folder tahun (mis. 2026) akan menampilkan pintasan
    | "Google Drive" di samping SPD & PBJ. Klik pintasan itu membuka folder Drive
    | yang sudah di-share (untuk menyimpan scan dokumen bertanda tangan/basah).
    |
    | Isi URL folder Google Drive per tahun. Ganti contoh di bawah dengan URL
    | folder Drive Anda. Kosongkan array bila belum ada.
    |
    */

    'gdrive_links' => [
        '2026' => 'https://drive.google.com/drive/folders/1o2baNePpaKav-EHnm-FiKy05fs2_kqjM?usp=sharing',
    ],

    // Dipakai bila suatu tahun tidak punya link khusus di atas (opsional).
    'gdrive_default' => null,

];
