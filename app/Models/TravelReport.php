<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TravelReport extends Model
{
    protected $fillable = [
        'travel_order_id',
        'nomor_surat_tugas',
        'tanggal_surat_tugas',
        'nomor_spd',
        'tanggal_spd',
        'prompt_latar_belakang',
        'prompt_kegiatan',
        'prompt_hasil',
        'prompt_kesimpulan',
        'prompt_penutup',
        'hasil_latar_belakang',
        'hasil_kegiatan',
        'hasil_dicapai',
        'hasil_kesimpulan',
        'hasil_penutup',
        'generated_at',
    ];

    protected $casts = [
        'tanggal_surat_tugas' => 'date',
        'tanggal_spd' => 'date',
        'generated_at' => 'datetime',
    ];

    public function travelOrder()
    {
        return $this->belongsTo(TravelOrder::class);
    }
}
