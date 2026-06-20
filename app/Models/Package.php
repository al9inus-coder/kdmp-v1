<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Package extends Model
{
    protected $fillable = [
    'import_batch_id',
    'fiscal_year_id',
    'program_id',
    'activity_id',
    'sub_activity_id',
    'account_id',

    'id_rup',
    'nama_paket',

    'pagu',

    'jenis_pengadaan',
    'metode_pengadaan',

    'pemilihan_mulai_bulan',
    'pemilihan_selesai_bulan',

    'kontrak_mulai_bulan',
    'kontrak_selesai_bulan',

    'status',

    'procurement_status',
    'target_procurement_date',
    'pptk_name',
    'ppk_name',
    'procurement_notes',

    'submitted_at',
    'submitted_by',
    'approved_at',
    'approved_by',
    ];

    protected $casts = [
    'pagu' => 'decimal:2',

    'pemilihan_mulai_bulan' => 'integer',
    'pemilihan_selesai_bulan' => 'integer',

    'kontrak_mulai_bulan' => 'integer',
    'kontrak_selesai_bulan' => 'integer',
    'target_procurement_date' => 'date',

    'submitted_at' => 'datetime',
    'approved_at' => 'datetime',
    ];

    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class);
    }
    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function subActivity(): BelongsTo
    {
        return $this->belongsTo(SubActivity::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function procurementPackage(): HasOne
    {
        return $this->hasOne(ProcurementPackage::class);
    }
    public static function monthNames(): array
    {
        return [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
    }
    public function isComplete(): bool
    {
        return
            !empty($this->nama_paket) &&
            !empty($this->sub_activity_id) &&
            !empty($this->account_id) &&
            !empty($this->jenis_pengadaan) &&
            !empty($this->metode_pengadaan) &&
            !empty($this->pemilihan_mulai_bulan) &&
            !empty($this->pemilihan_selesai_bulan) &&
            !empty($this->kontrak_mulai_bulan) &&
            !empty($this->kontrak_selesai_bulan);
    }

    public function getRouteKey()
    {
        return $this->id_rup ?? $this->id;
    }

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('id_rup', $value)
                    ->orWhere('id', $value)
                    ->firstOrFail();
    }
}
