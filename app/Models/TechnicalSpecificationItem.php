<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TechnicalSpecificationItem extends Model
{
    protected $fillable = [
        'technical_specification_id',
        'nama_barang_jasa',
        'spesifikasi',
        'volume',
        'satuan',
        'harga_satuan_dpa',
        'pdn',
        'tkdn',
        'kode_mak',
        'urutan',
    ];

    protected $casts = [
        'volume' => 'decimal:2',
        'pdn' => 'boolean',
        'tkdn' => 'decimal:2',
        'urutan' => 'integer',
    ];

    public function technicalSpecification(): BelongsTo
    {
        return $this->belongsTo(TechnicalSpecification::class);
    }
}
