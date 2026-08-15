<?php

namespace App\Services\Pajak;

use App\Models\TarifPajak;

/**
 * Skema menentukan pajak APA SAJA yang berlaku bersamaan pada satu pengadaan.
 *
 * Sengaja tidak dijadikan data, berbeda dengan tarifnya. Tiap skema mengubah
 * isi baris potongan pada BAP, jadi menambah skema baru selalu menuntut
 * penyesuaian dokumen — menjadikannya data hanya memindahkan kerumitan tanpa
 * menghilangkannya. Tarifnya tetap bisa diubah operator lewat /admin/pajak.
 */
class SkemaPajak
{
    public const STANDAR = 'standar';
    public const RESTORAN = 'restoran';
    public const KONSTRUKSI = 'konstruksi';

    /**
     * Nama skema untuk ditampilkan di form dan halaman kelola.
     */
    public static function pilihan(): array
    {
        return [
            self::STANDAR => 'Standar — PPN + PPh 22/23',
            self::RESTORAN => 'Restoran — pajak restoran menggantikan PPN',
            self::KONSTRUKSI => 'Konstruksi — PPh Final 4(2) menggantikan PPh 22/23',
        ];
    }

    /**
     * Pajak konsumsi yang berlaku: PPN pada skema standar dan konstruksi,
     * pajak restoran pada skema restoran. Selalu ada satu, dan nilainya sudah
     * termasuk di dalam nilai kontrak — itu yang menentukan pembagi DPP.
     */
    public static function jenisPajakKonsumsi(string $skema): string
    {
        return $skema === self::RESTORAN
            ? TarifPajak::PAJAK_RESTORAN
            : TarifPajak::PPN;
    }

    /**
     * Pajak penghasilan yang berlaku. Konstruksi memakai PPh Final 4(2)
     * menggantikan PPh 22/23; selain itu ditentukan jenis pengadaannya.
     */
    public static function jenisPajakPenghasilan(string $skema, ?string $jenisPengadaan): string
    {
        if ($skema === self::KONSTRUKSI) {
            return TarifPajak::PPH4_2_KONSTRUKSI;
        }

        return str_contains(strtolower((string) $jenisPengadaan), 'barang')
            ? TarifPajak::PPH22_BARANG
            : TarifPajak::PPH23_JASA;
    }

    public static function valid(?string $skema): bool
    {
        return array_key_exists((string) $skema, self::pilihan());
    }
}
