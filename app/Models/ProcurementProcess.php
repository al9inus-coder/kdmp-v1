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

    public function procurementPackage(): BelongsTo
    {
        return $this->belongsTo(ProcurementPackage::class);
    }
}
