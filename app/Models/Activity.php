<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    protected $fillable = [
        'program_id',
        'kode',
        'nama',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Kegiatan yang berjalan: dirinya dan programnya sama-sama aktif.
     * Menonaktifkan program otomatis menutup kegiatan di bawahnya.
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->whereHas('program', fn (Builder $q) => $q->aktif());
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function subActivities(): HasMany
    {
        return $this->hasMany(SubActivity::class);
    }

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }
}
