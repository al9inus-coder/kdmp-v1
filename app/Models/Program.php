<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    protected $fillable = [
        'skpd_id',
        'kode',
        'nama',
        'tahun',
        'pagu'
    ];

    public function skpd()
    {
        return $this->belongsTo(Skpd::class);
    }
}