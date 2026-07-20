<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SbuTiketPesawat extends Model
{
    protected $fillable = [
        'tujuan',
        'satuan',
        'bisnis',
        'ekonomi',
    ];
}
