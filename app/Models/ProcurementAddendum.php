<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcurementAddendum extends Model
{
    protected $fillable = [
        'procurement_package_id',
        'nomor',
        'tanggal_akhir_baru',
        'alasan',
    ];

    protected $casts = [
        'tanggal_akhir_baru' => 'date',
    ];

    public function procurementPackage()
    {
        return $this->belongsTo(ProcurementPackage::class);
    }
}
