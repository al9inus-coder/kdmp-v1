<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProcurementPackage extends Model
{
    public const WORKFLOW_DRAFT = 'draft';
    public const WORKFLOW_PROVIDER_SELECTION = 'provider_selection';
    public const WORKFLOW_EXECUTION = 'execution';
    public const WORKFLOW_PAYMENT_PROCESS = 'payment_process';
    public const WORKFLOW_COMPLETED = 'completed';

    public static function getWorkflowStatuses(): array
    {
        return [
            self::WORKFLOW_DRAFT => 'Persiapan Pengadaan',
            self::WORKFLOW_PROVIDER_SELECTION => 'Pemilihan Penyedia',
            self::WORKFLOW_EXECUTION => 'Pelaksanaan',
            self::WORKFLOW_PAYMENT_PROCESS => 'Pembayaran',
            self::WORKFLOW_COMPLETED => 'Selesai',
        ];
    }

    protected $fillable = [
    'package_id',
    'status',
    'workflow_status',
    'number',
    'created_by',
    'nama_ppk',
    'user_ppk',
    'pangkat_gol_ppk',
    'nip_ppk',
    'no_telp_ppk',
    'email_ppk',
    'npwp_instansi',
    'jenis_kontrak',
    'jangka_waktu',
    'jangka_waktu_jenis',
    'jangka_waktu_nilai',
    'jangka_waktu_satuan',
    'ada_garansi',
    'garansi_nilai',
    'garansi_satuan',
    'layanan_purna_jual',
    'dikecualikan_type',
    'tanggal_barang_diterima',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function technicalSpecification(): HasOne
    {
        return $this->hasOne(TechnicalSpecification::class);
    }

    public function procurementRequest(): HasOne
    {
        return $this->hasOne(ProcurementRequest::class);
    }

    public function priceReferences(): HasMany
    {
        return $this->hasMany(PriceReference::class);
    }

    public function procurementProcess(): HasOne
    {
        return $this->hasOne(ProcurementProcess::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(ProcurementPayment::class);
    }

    public function addendums(): HasMany
    {
        return $this->hasMany(ProcurementAddendum::class);
    }

    public function externalRecords()
    {
        return $this->hasMany(ProcurementExternalRecord::class);
    }

    public function getRealisasiAttribute()
    {
        // Jika metode Dikecualikan
        if ($this->package && $this->package->metode_pengadaan == 'Dikecualikan') {
            return $this->externalRecords->sum('nilai_kontrak');
        }

        // Jika metode E-Purchasing atau lainnya yang menggunakan procurementProcess
        return $this->procurementProcess ? $this->procurementProcess->nilai_kontrak : 0;
    }

    /**
     * Auto-fill PPK dari Master SKPD (bersifat snapshot).
     * Jika field PPK di ProcurementPackage masih kosong, ambil dari SKPD.
     */
    public function syncPpkFromSkpd(): void
    {
        $skpd = Skpd::first();
        if (!$skpd) return;

        $changed = false;

        $fields = [
            'nama_ppk' => 'nama_ppk',
            'nip_ppk' => 'nip_ppk',
            'pangkat_gol_ppk' => 'pangkat_ppk',
            'no_telp_ppk' => 'telepon_ppk',
            'email_ppk' => 'email_ppk',
            'user_ppk' => 'username_ppk',
            'npwp_instansi' => 'npwp_dinas',
        ];

        foreach ($fields as $procField => $skpdField) {
            if (empty($this->{$procField}) && !empty($skpd->{$skpdField})) {
                $this->{$procField} = $skpd->{$skpdField};
                $changed = true;
            }
        }

        if ($changed) {
            $this->save();
        }
    }
}
