<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SbuPenginapan extends Model
{
    protected $fillable = [
        'provinsi',
        'satuan',
        'eselon_ii',
        'eselon_iii',
        'eselon_iv',
    ];
}
