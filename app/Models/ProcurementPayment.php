<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcurementPayment extends Model
{
    protected $fillable = [
        'procurement_package_id',
        'nomor_bast',
        'tanggal_bast',
        'nomor_invoice',
        'tanggal_invoice',
        'nomor_bap',
        'tanggal_bap',
        'nomor_kwitansi',
        'tanggal_kwitansi',
        'is_non_pkp',
        'tanggal_non_pkp',
        'tanggal_ringkasan_kontrak',
        'nama_pptk',
        'nip_pptk',
        'pangkat_golongan_pptk',
        'skema_pajak',
        'jenis_pph',
        'kualifikasi_pajak',
        'nilai_kontrak_fix',
    ];

    protected $casts = [
        'nilai_kontrak_fix' => 'decimal:2',
        'tanggal_bast' => 'date',
        'tanggal_invoice' => 'date',
        'tanggal_bap' => 'date',
        'tanggal_kwitansi' => 'date',
        'tanggal_non_pkp' => 'date',
        'tanggal_ringkasan_kontrak' => 'date',
        'is_non_pkp' => 'boolean',
    ];

    /**
     * Pajak yang diterapkan pada pembayaran ini. Kosong berarti tidak ada
     * pajak yang dipungut — keadaan yang sah, bukan data yang hilang.
     */
    public function pajaks()
    {
        return $this->hasMany(ProcurementPaymentPajak::class)->orderBy('urutan');
    }

    public function procurementPackage()
    {
        return $this->belongsTo(ProcurementPackage::class);
    }
}
