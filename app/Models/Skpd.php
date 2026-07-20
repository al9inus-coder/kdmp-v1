<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skpd extends Model
{
    protected $fillable = [
        'kode',
        'nama',
        'singkatan',
        'alamat',
        'npwp_dinas',
        'kepala_skpd',
        'nip_kepala',
        'nama_ppk',
        'nip_ppk',
        'pangkat_ppk',
        'telepon_ppk',
        'email_ppk',
        'username_ppk',
        'nama_pptk',
        'nip_pptk',
        'pangkat_pptk',
        'nama_bendahara',
        'nip_bendahara',
    ];

    public function programs()
    {
        return $this->hasMany(Program::class);
    }
}