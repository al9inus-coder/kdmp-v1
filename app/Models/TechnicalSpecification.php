<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TechnicalSpecification extends Model
{
    protected $fillable = [
        'procurement_package_id',
        'latar_belakang',
        'maksud',
        'target_sasaran',
        'uraian_pekerjaan',
        'jangka_waktu',
        'jangka_waktu_jenis',
        'garansi_nilai',
        'garansi_satuan',
        'layanan_purna_jual',
        'jenis_kontrak',
        'npwp_instansi',
        'nama_ppk',
        'pangkat_gol_ppk',
        'nip_ppk',
        'no_telp_ppk',
        'email_ppk',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'jangka_waktu' => 'integer',
        'garansi_nilai' => 'integer',
        'layanan_purna_jual' => 'boolean',
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

    public function items(): HasMany
    {
        return $this->hasMany(TechnicalSpecificationItem::class)->orderBy('urutan');
    }
}
