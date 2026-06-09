<?php

namespace App\Models;

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
