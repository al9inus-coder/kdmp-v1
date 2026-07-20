<?php

if (!function_exists('rupiahSingkat')) {

    /**
     * Format rupiah ringkas dengan satuan Indonesia yang benar:
     * M = miliar, Jt = juta, Rb = ribu.
     * Contoh: 1.746.229.000 => "Rp 1,75 M" ; 105.060.000 => "Rp 105,06 Jt".
     */
    function rupiahSingkat($nilai): string
    {
        $nilai   = (float) $nilai;
        $negatif = $nilai < 0 ? '-' : '';
        $abs     = abs($nilai);

        $trim = function (string $angka): string {
            // Buang nol desimal yang tidak perlu: "1,50" => "1,5" ; "105,00" => "105"
            if (str_contains($angka, ',')) {
                $angka = rtrim(rtrim($angka, '0'), ',');
            }
            return $angka;
        };

        if ($abs >= 1_000_000_000) {
            return $negatif.'Rp '.$trim(number_format($abs / 1_000_000_000, 2, ',', '.')).' M';
        }

        if ($abs >= 1_000_000) {
            return $negatif.'Rp '.$trim(number_format($abs / 1_000_000, 2, ',', '.')).' Jt';
        }

        if ($abs >= 1_000) {
            return $negatif.'Rp '.$trim(number_format($abs / 1_000, 1, ',', '.')).' Rb';
        }

        return $negatif.'Rp '.number_format($abs, 0, ',', '.');
    }
}
