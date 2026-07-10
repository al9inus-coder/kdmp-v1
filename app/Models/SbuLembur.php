<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SbuLembur extends Model
{
    use HasFactory;

    protected $fillable = [
        'jenis',
        'golongan',
        'satuan',
        'besaran',
    ];

    /**
     * Normalisasi golongan pegawai ke token: 'IV' | 'III' | 'II' | 'I' | 'P3K'.
     * Toleran terhadap variasi format ("Penata Tk.I/III-d", "III/d", "Golongan III", dst).
     * Golongan VII (P3K) disetarakan Golongan II — mengikuti aturan lama.
     */
    public static function golonganToken(?string $golongan): string
    {
        $g = strtoupper(trim((string) $golongan));

        if ($g === '' || $g === '-') return 'P3K';
        if (preg_match('/\bVII\b/', $g)) return 'II';
        if (preg_match('/\bIV\b/', $g)) return 'IV';
        if (preg_match('/\bIII\b/', $g)) return 'III';
        if (preg_match('/\bII\b/', $g)) return 'II';
        if (preg_match('/\bI\b/', $g)) return 'I';

        return 'P3K';
    }

    /**
     * Apakah label golongan pada baris SBU cocok dengan token pegawai.
     * Label bebas format: "IV", "Golongan IV", "Golongan II dan Golongan I", "P3K Paruh Waktu".
     */
    public static function labelMatchesToken(?string $label, string $token): bool
    {
        $l = strtoupper(trim((string) $label));

        if ($token === 'P3K') {
            return str_contains($l, 'P3K');
        }

        preg_match_all('/\b(IV|III|II|I)\b/', $l, $m);

        return in_array($token, $m[1] ?? [], true);
    }

    /**
     * Cari tarif SBU untuk jenis + golongan pegawai dari koleksi yang sudah dimuat.
     * Mengembalikan null bila tidak ada baris yang cocok — sengaja TIDAK menebak
     * tarif lain agar ketidaksinkronan data langsung terlihat, bukan salah bayar diam-diam.
     */
    public static function pickRate($sbuRates, string $jenis, ?string $empGolongan): ?self
    {
        $token = self::golonganToken($empGolongan);

        return collect($sbuRates)->first(
            fn ($r) => $r->jenis === $jenis && self::labelMatchesToken($r->golongan, $token)
        );
    }
}
