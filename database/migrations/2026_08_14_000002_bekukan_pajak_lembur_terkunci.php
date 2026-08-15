<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Membekukan persentase pajak pada periode lembur yang SUDAH dikunci, memakai
 * aturan lama yang berlaku saat periode itu ditutup.
 *
 * Tanpa ini, memindahkan tarif pajak ke data akan diam-diam menghitung ulang
 * bulan yang sudah dibayarkan: petugas kebersihan dulu 0% karena golongannya
 * kosong, sekarang 5% sebagai PPPK. Di basis data pengembangan saja itu
 * menyentuh 163 baris di 7 periode.
 *
 * Aturan lama sengaja ditiru apa adanya, termasuk cacatnya — pencocokan
 * potongan kata, sehingga golongan yang ditulis "VIII" dulu terbaca sebagai
 * Golongan III. Yang dibekukan harus angka yang benar-benar dipakai waktu itu,
 * bukan angka yang seharusnya.
 */
return new class extends Migration
{
    public function up(): void
    {
        $baris = DB::table('overtime_details')
            ->join('overtimes', 'overtimes.id', '=', 'overtime_details.overtime_id')
            ->leftJoin('employees', 'employees.id', '=', 'overtime_details.employee_id')
            ->where('overtimes.is_locked', true)
            ->whereNull('overtime_details.persen_pajak_fix')
            ->select(
                'overtime_details.id',
                'overtime_details.golongan_fix',
                'employees.golongan as golongan_pegawai'
            )
            ->get();

        foreach ($baris as $b) {
            // golongan_fix adalah salinan saat penguncian; itu yang paling
            // mewakili keadaan waktu itu. Pegawai bisa saja naik golongan sesudahnya.
            $golongan = strtoupper((string) ($b->golongan_fix ?: $b->golongan_pegawai));

            $persen = 0;
            if (str_contains($golongan, 'III')) {
                $persen = 5;
            } elseif (str_contains($golongan, 'IV')) {
                $persen = 15;
            }

            DB::table('overtime_details')->where('id', $b->id)->update(['persen_pajak_fix' => $persen]);
        }
    }

    public function down(): void
    {
        DB::table('overtime_details')
            ->join('overtimes', 'overtimes.id', '=', 'overtime_details.overtime_id')
            ->where('overtimes.is_locked', true)
            ->update(['overtime_details.persen_pajak_fix' => null]);
    }
};
