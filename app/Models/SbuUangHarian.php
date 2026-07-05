<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SbuUangHarian extends Model
{
    use HasFactory;

    protected $fillable = [
        'provinsi',
        'satuan',
        'luar_kota',
        'diklat',
    ];
}
