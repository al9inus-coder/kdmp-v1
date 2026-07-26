<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    protected $fillable = [
        'kode',
        'nama',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Program yang dijalankan tahun ini. Program non-aktif dianggap tidak ada
     * di DPA, jadi tidak boleh muncul di Anggaran maupun Monev.
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }
}
