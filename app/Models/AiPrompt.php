<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiPrompt extends Model
{
    protected $fillable = [
        'code',
        'name',
        'prompt',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}