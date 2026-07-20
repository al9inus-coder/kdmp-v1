<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Catatan koreksi data bisnis. Bersifat append-only: tidak ada endpoint
 * update/delete — koreksi yang keliru diperbaiki dengan koreksi baru.
 */
class DataCorrection extends Model
{
    protected $fillable = [
        'object_type',
        'object_id',
        'target_type',
        'target_id',
        'field_key',
        'field_label',
        'old_value',
        'new_value',
        'reason',
        'attachment_path',
        'user_id',
        'ip_address',
        'user_agent',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
