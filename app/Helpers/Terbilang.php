<?php

namespace App\Helpers;

class Terbilang
{
    public static function make($number)
    {
        $number = abs($number);
        $huruf = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];
        $temp = "";

        if ($number < 12) {
            $temp = " " . $huruf[$number];
        } else if ($number < 20) {
            $temp = self::make($number - 10) . " Belas";
        } else if ($number < 100) {
            $temp = self::make($number / 10) . " Puluh " . self::make($number % 10);
        } else if ($number < 200) {
            $temp = " Seratus " . self::make($number - 100);
        } else if ($number < 1000) {
            $temp = self::make($number / 100) . " Ratus " . self::make($number % 100);
        } else if ($number < 2000) {
            $temp = " Seribu " . self::make($number - 1000);
        } else if ($number < 1000000) {
            $temp = self::make($number / 1000) . " Ribu " . self::make($number % 1000);
        } else if ($number < 1000000000) {
            $temp = self::make($number / 1000000) . " Juta " . self::make($number % 1000000);
        } else if ($number < 1000000000000) {
            $temp = self::make($number / 1000000000) . " Milyar " . self::make(fmod($number, 1000000000));
        } else if ($number < 1000000000000000) {
            $temp = self::make($number / 1000000000000) . " Trilyun " . self::make(fmod($number, 1000000000000));
        }

        return trim(preg_replace('/\s+/', ' ', $temp));
    }
}
