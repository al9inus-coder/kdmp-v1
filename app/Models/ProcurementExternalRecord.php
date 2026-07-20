<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcurementExternalRecord extends Model
{
    protected $fillable = [
        'procurement_package_id',
        'surat_pesanan_no',
        'surat_pesanan_tgl',
        'surat_tagihan_no',
        'surat_tagihan_tgl',
        'bast_no',
        'bast_tgl',
        'bap_no',
        'bap_tgl',
        'kwitansi_no',
        'kwitansi_tgl',
        'nilai_kontrak',
    ];

    public function procurementPackage()
    {
        return $this->belongsTo(ProcurementPackage::class);
    }
}
