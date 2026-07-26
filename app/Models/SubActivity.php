<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubActivity extends Model
{
    protected $fillable = [
        'activity_id',
        'kode',
        'nama',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Sub kegiatan yang benar-benar dijalankan di DPA: dirinya, kegiatan
     * induknya, dan programnya harus sama-sama aktif. Satu saja dimatikan,
     * seluruh cabang di bawahnya ikut tertutup.
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->whereHas('activity', fn (Builder $q) => $q->aktif());
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }
}
