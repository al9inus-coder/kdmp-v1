<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SbuTransportRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'tempat_kedudukan',
        'tempat_tujuan',
        'satuan',
        'biaya_mobil',
        'biaya_motor',
    ];
}
