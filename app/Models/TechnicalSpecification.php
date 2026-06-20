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
        'tanggal',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
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

    public function getMaksudAttribute($value)
    {
        if (empty($value)) return ['Maksud' => '', 'Tujuan' => ''];
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return array_merge(['Maksud' => '', 'Tujuan' => ''], $decoded);
        }
        return ['Maksud' => $value, 'Tujuan' => ''];
    }

    public function setMaksudAttribute($value)
    {
        $this->attributes['maksud'] = is_array($value) ? json_encode($value) : $value;
    }

    public function getTargetSasaranAttribute($value)
    {
        if (empty($value)) return ['Target' => '', 'Sasaran' => ''];
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return array_merge(['Target' => '', 'Sasaran' => ''], $decoded);
        }
        return ['Target' => $value, 'Sasaran' => ''];
    }

    public function setTargetSasaranAttribute($value)
    {
        $this->attributes['target_sasaran'] = is_array($value) ? json_encode($value) : $value;
    }
}
