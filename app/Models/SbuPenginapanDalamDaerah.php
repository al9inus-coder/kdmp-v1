<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SbuPenginapanDalamDaerah extends Model
{
    protected $fillable = [
        'tempat_tujuan',
        'satuan',
        'eselon_ii',
        'eselon_iii',
        'eselon_iv',
        'golongan_i_ii'
    ];
}
