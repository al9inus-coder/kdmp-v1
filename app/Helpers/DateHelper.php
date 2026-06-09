<?php

if (!function_exists('bulanIndonesia')) {

    function bulanIndonesia(?int $bulan): string
    {
        $bulanList = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $bulanList[$bulan] ?? '-';
    }
}

if (!function_exists('tanggalIndonesia')) {

    function tanggalIndonesia($tanggal): string
    {
        if (empty($tanggal)) {
            return '-';
        }

        return \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y');
    }
}

if (!function_exists('tanggalWaktuIndonesia')) {

    function tanggalWaktuIndonesia($tanggal): string
    {
        if (empty($tanggal)) {
            return '-';
        }

        return \Carbon\Carbon::parse($tanggal)
            ->translatedFormat('d F Y H:i');
    }
}

if (!function_exists('daftarBulanIndonesia')) {

    function daftarBulanIndonesia(): array
    {
        return [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
    }
}