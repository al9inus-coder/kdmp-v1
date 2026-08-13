<?php

namespace App\Helpers;

class Terbilang
{
    public static function make($number)
    {
        // Dibulatkan ke bawah sejak awal. Nilai yang masuk bisa berupa string
        // desimal dari kolom decimal ("19550000.00") atau float, dan sisa
        // pecahannya memang tidak pernah ikut dieja — dulu itu terjadi lewat
        // pemotongan diam-diam saat dipakai sebagai indeks array, yang sejak
        // PHP 8.1 memunculkan peringatan deprecated setiap kali dokumen dicetak.
        // null ikut dijaga: dokumen bisa dicetak untuk paket yang nilai
        // kontraknya belum diisi, dan abs(null) sendiri sudah deprecated.
        $number = (int) abs($number ?? 0);

        $huruf = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];
        $temp = "";

        // intdiv, bukan pembagian biasa: hasil bagi harus bilangan bulat
        // sebelum dieja ulang.
        if ($number < 12) {
            $temp = " " . $huruf[$number];
        } else if ($number < 20) {
            $temp = self::make($number - 10) . " Belas";
        } else if ($number < 100) {
            $temp = self::make(intdiv($number, 10)) . " Puluh " . self::make($number % 10);
        } else if ($number < 200) {
            $temp = " Seratus " . self::make($number - 100);
        } else if ($number < 1000) {
            $temp = self::make(intdiv($number, 100)) . " Ratus " . self::make($number % 100);
        } else if ($number < 2000) {
            $temp = " Seribu " . self::make($number - 1000);
        } else if ($number < 1000000) {
            $temp = self::make(intdiv($number, 1000)) . " Ribu " . self::make($number % 1000);
        } else if ($number < 1000000000) {
            $temp = self::make(intdiv($number, 1000000)) . " Juta " . self::make($number % 1000000);
        } else if ($number < 1000000000000) {
            $temp = self::make(intdiv($number, 1000000000)) . " Milyar " . self::make($number % 1000000000);
        } else if ($number < 1000000000000000) {
            $temp = self::make(intdiv($number, 1000000000000)) . " Trilyun " . self::make($number % 1000000000000);
        }

        return trim(preg_replace('/\s+/', ' ', $temp));
    }
}
