<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetRevision extends Model
{
    public const JENIS_MURNI = 'murni';
    public const JENIS_PERGESERAN = 'pergeseran';
    public const JENIS_PERUBAHAN = 'perubahan';

    protected $fillable = [
        'budget_line_id',
        'jenis',
        'urutan',
        'tanggal',
        'nomor_dasar',
        'pagu',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'pagu' => 'decimal:2',
        'urutan' => 'integer',
    ];

    public static function jenisOptions(): array
    {
        return [
            self::JENIS_MURNI => 'APBD Murni',
            self::JENIS_PERGESERAN => 'Pergeseran',
            self::JENIS_PERUBAHAN => 'APBD Perubahan',
        ];
    }

    public function budgetLine(): BelongsTo
    {
        return $this->belongsTo(BudgetLine::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * "APBD Murni", "Pergeseran ke-2", "APBD Perubahan".
     * Nomor urut hanya bermakna untuk pergeseran yang bisa berulang.
     */
    public function getLabelAttribute(): string
    {
        $nama = self::jenisOptions()[$this->jenis] ?? ucfirst((string) $this->jenis);

        if ($this->jenis === self::JENIS_PERGESERAN && $this->urutan > 0) {
            return $nama . ' ke-' . $this->urutan;
        }

        return $nama;
    }
}
