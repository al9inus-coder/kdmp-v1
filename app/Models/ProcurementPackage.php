<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProcurementPackage extends Model
{
    protected $fillable = [

    'package_id',
    'status',
    'number',
    'created_by',
    'nama_ppk',
    'user_ppk',
    'pangkat_gol_ppk',
    'nip_ppk',
    'no_telp_ppk',
    'email_ppk',
    'npwp_instansi',
    'jenis_kontrak',
    'jangka_waktu',
    'jangka_waktu_jenis',
    'jangka_waktu_nilai',
    'jangka_waktu_satuan',
    'ada_garansi',
    'garansi_nilai',
    'garansi_satuan',
    'layanan_purna_jual',
    'tanggal_barang_diterima',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function technicalSpecification(): HasOne
    {
        return $this->hasOne(TechnicalSpecification::class);
    }

    public function procurementRequest(): HasOne
    {
        return $this->hasOne(ProcurementRequest::class);
    }

    public function priceReferences(): HasMany
    {
        return $this->hasMany(PriceReference::class);
    }
}
