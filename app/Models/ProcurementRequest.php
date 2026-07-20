<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcurementRequest extends Model
{
    protected $fillable = [
        'procurement_package_id',
        'nomor_surat',
        'tanggal_surat',
        'nama_pejabat_pengadaan',
        'nama_penyedia',
        'alasan_pemilihan_penyedia',
        'isi_surat',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
    ];

    public function procurementPackage(): BelongsTo
    {
        return $this->belongsTo(ProcurementPackage::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
