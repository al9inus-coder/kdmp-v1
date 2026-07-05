<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SbuLembur extends Model
{
    use HasFactory;

    protected $fillable = [
        'jenis',
        'golongan',
        'satuan',
        'besaran',
    ];
}
