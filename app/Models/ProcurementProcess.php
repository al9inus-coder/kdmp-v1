<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcurementProcess extends Model
{
    protected $fillable = [
        'procurement_package_id',
        'nomor_surat_pesanan',
        'tanggal_surat_pesanan',
        'nama_penyedia',
        'alamat_penyedia',
        'npwp_penyedia',
        'waktu_pelaksanaan_nilai',
        'waktu_pelaksanaan_satuan',
        'tanggal_barang_diterima',
        'catatan',
        'nilai_kontrak',
        'nomor_rekening',
        'nama_bank',
        'nama_pic',
        'jabatan_pic',
        'tanggal_mulai_kontrak',
    ];

    protected $casts = [
        'tanggal_surat_pesanan' => 'date',
        'tanggal_barang_diterima' => 'date',
        'tanggal_mulai_kontrak' => 'date',
    ];

    /**
     * Lama pelaksanaan kontrak dalam hari kalender, dihitung INKLUSIF —
     * hari pertama dan hari terakhir ikut terhitung, sesuai kelaziman
     * pengadaan: 27 s/d 31 Juli adalah 5 hari, bukan 4.
     *
     * Disatukan di sini karena angka ini sebelumnya dihitung di lima tempat
     * dengan tiga rumus berbeda, sehingga dokumen cetak menyebut angka yang
     * berbeda dari yang tampil di layar untuk kontrak yang sama.
     *
     * Mengembalikan null bila salah satu tanggalnya belum diisi — pemanggil
     * yang menentukan apa yang pantas ditampilkan saat itu.
     */
    public function durasiHari(): ?int
    {
        if (!$this->tanggal_surat_pesanan || !$this->tanggal_barang_diterima) {
            return null;
        }

        // diffInDays pada Carbon 3 mengembalikan float; dibulatkan ke int
        // supaya tidak ada pecahan yang bocor ke dokumen maupun ke Terbilang.
        return (int) $this->tanggal_surat_pesanan->diffInDays($this->tanggal_barang_diterima) + 1;
    }

    public function procurementPackage(): BelongsTo
    {
        return $this->belongsTo(ProcurementPackage::class);
    }
}
