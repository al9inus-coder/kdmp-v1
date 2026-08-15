<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu pajak yang diterapkan pada satu pembayaran.
 *
 * Ada barisnya berarti dipungut, tidak ada berarti tidak dipungut — tanpa nilai
 * sentinel. Sebelumnya jenis pajak konsumsi tersimpan tersirat lewat kolom mana
 * yang terisi (persen_ppn_fix vs persen_restoran_fix), sehingga dua kolom
 * memikul satu fakta dan bisa berselisih dengan skema_pajak.
 *
 * nilai_dasar dan nominal adalah rupiah SAAT DISIMPAN. BAP mencatat apa yang
 * terjadi pada tanggalnya, bukan hasil hitungan hari ini — jadi mengubah tarif
 * di /admin/pajak maupun mengubah nilai kontrak tidak boleh menggesernya.
 */
class ProcurementPaymentPajak extends Model
{
    protected $table = 'procurement_payment_pajaks';

    protected $fillable = [
        'procurement_payment_id',
        'jenis',
        'kunci',
        'persen',
        'dasar',
        'nilai_dasar',
        'nominal',
        'urutan',
    ];

    protected $casts = [
        'persen' => 'decimal:2',
        'nilai_dasar' => 'decimal:2',
        'nominal' => 'decimal:2',
        'urutan' => 'integer',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(ProcurementPayment::class, 'procurement_payment_id');
    }
}
