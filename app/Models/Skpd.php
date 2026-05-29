<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skpd extends Model
{
    protected $fillable = [
        'kode',
        'nama',
        'alamat',
        'kepala_skpd',
    ];

    public function programs()
    {
        return $this->hasMany(Program::class);
    }
}