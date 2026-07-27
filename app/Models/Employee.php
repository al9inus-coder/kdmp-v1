<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    /**
     * Pegawai dinas — yang bisa ditugaskan perjalanan dinas dan lembur bidang.
     * Petugas kebersihan sengaja dipisah: jumlahnya puluhan dan hanya muncul
     * pada lembur kebersihan, jadi tidak boleh membanjiri daftar lain.
     */
    public function scopeDinas(Builder $query): Builder
    {
        return $query->where('tipe', self::TIPE_DINAS);
    }

    public function scopeKebersihan(Builder $query): Builder
    {
        return $query->where('tipe', self::TIPE_KEBERSIHAN);
    }
}
