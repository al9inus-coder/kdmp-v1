<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'nama',
        'nip',
        'golongan',
        'jabatan',
        'tanggal_lahir',
        'kategori_biaya',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];
}
