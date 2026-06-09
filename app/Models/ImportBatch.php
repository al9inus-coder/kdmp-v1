<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportBatch extends Model
{
    protected $fillable = [
    'fiscal_year_id',
    'created_by',

    'file_name',
    'file_path',

    'total_rows',
    'success_rows',
    'failed_rows',

    'status',
    'imported_at',
    'notes',
    ];

    protected $casts = [
        'total_rows' => 'integer',
        'success_rows' => 'integer',
        'failed_rows' => 'integer',
        'imported_at' => 'datetime',
    ];

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }
    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function errors()
    {
        return $this->hasMany(ImportBatchError::class);
    }
}
