<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Acuan tarif pajak — satu tabel untuk seluruh pajak yang dipakai aplikasi.
 *
 * Disimpan sebagai data agar bisa disesuaikan lewat /admin/pajak, bukan lewat
 * rilis kode. Yang TIDAK ikut jadi data adalah skema (pajak apa saja yang
 * berlaku bersamaan) — lihat App\Services\Pajak\SkemaPajak untuk alasannya.
 */
class TarifPajak extends Model
{
    public const PPH21 = 'pph21';
    public const PPN = 'ppn';
    public const PPH22_BARANG = 'pph22_barang';
    public const PPH23_JASA = 'pph23_jasa';
    public const PAJAK_RESTORAN = 'pajak_restoran';
    public const PPH4_2_KONSTRUKSI = 'pph4_2_konstruksi';

    // Kosakata dasar pengenaan tinggal di App\Services\Pajak\PajakPengadaan
    // (pilihanDasar()), sebab di sanalah ia menggerakkan perhitungan. Menyimpan
    // salinannya di sini pernah membuat keduanya berselisih: tabel memakai
    // 'dpp' sementara perhitungan hanya mengenali 'setelah_ppn'/'setelah_pbjt',
    // sehingga setiap usulan diam-diam jatuh ke bruto.

    protected $fillable = [
        'jenis',
        'kunci',
        'persen',
        'dasar',
        'keterangan',
        'aktif',
    ];

    protected $casts = [
        'persen' => 'decimal:2',
        'aktif' => 'boolean',
    ];

    /**
     * Nama jenis untuk ditampilkan, sekaligus daftar jenis yang dikenali.
     */
    public static function jenisOptions(): array
    {
        return [
            self::PPH21 => 'PPh 21 — Pajak Penghasilan',
            self::PPN => 'PPN',
            self::PPH22_BARANG => 'PPh 22 — Barang',
            self::PPH23_JASA => 'PPh 23 — Jasa',
            self::PAJAK_RESTORAN => 'Pajak Restoran',
            self::PPH4_2_KONSTRUKSI => 'PPh Final Pasal 4(2) — Konstruksi',
        ];
    }

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    /**
     * Tarif untuk jenis yang berlaku menyeluruh — PPN, PPh 22, PPh 23,
     * pajak restoran. Yang berkunci dicari lewat untukGolongan/untukKunci.
     */
    public static function untukJenis($tarif, string $jenis): ?self
    {
        return collect($tarif)->first(
            fn ($t) => $t->jenis === $jenis && $t->aktif
        );
    }

    /**
     * Tarif PPh 21 menurut golongan pegawai — dipakai lembur, dan nanti
     * honorarium serta penghasilan lain yang dipotong per golongan.
     *
     * Pencocokannya memakai aturan yang sama dengan tarif SBU
     * (SbuLembur::golonganToken + labelMatchesToken), bukan pencarian potongan
     * kata. Itu yang menutup jebakan lama: golongan P3K yang ditulis "VIII"
     * atau "XIII" dulu terbaca sebagai Golongan III lalu dipotong 5%.
     */
    public static function untukGolongan($tarif, ?string $golongan): ?self
    {
        $token = SbuLembur::golonganToken($golongan);

        return collect($tarif)->first(
            fn ($t) => $t->jenis === self::PPH21
                && $t->aktif
                && SbuLembur::labelMatchesToken($t->kunci, $token)
        );
    }

    /**
     * Tarif berkunci selain golongan — sekarang hanya kualifikasi penyedia
     * pada PPh Final 4(2). Dicocokkan persis, sebab kualifikasi dipilih dari
     * daftar, bukan diketik bebas seperti golongan.
     */
    public static function untukKunci($tarif, string $jenis, ?string $kunci): ?self
    {
        return collect($tarif)->first(
            fn ($t) => $t->jenis === $jenis && $t->aktif && $t->kunci === $kunci
        );
    }
}
