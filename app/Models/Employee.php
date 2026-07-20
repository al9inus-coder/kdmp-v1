<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    public const TIPE_DINAS = 'dinas';
    public const TIPE_KEBERSIHAN = 'kebersihan';

    protected $fillable = [
        'nama',
        'nip',
        'golongan',
        'jabatan',
        'tanggal_lahir',
        'kategori_biaya',
        'tipe',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];
}
