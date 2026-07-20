<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceReference extends Model
{
    protected $fillable = [
        'procurement_package_id',
        'nama_barang_jasa',
        'nama_produk_etalase',
        'volume',
        'satuan',
        'nama_pelaku_usaha',
        'harga_satuan',
        'jumlah_harga',
        'link_produk',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'volume' => 'decimal:2',
        'harga_satuan' => 'decimal:2',
        'jumlah_harga' => 'decimal:2',
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
