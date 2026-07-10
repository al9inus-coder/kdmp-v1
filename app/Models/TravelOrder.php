<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TravelOrder extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REVISION = 'revision';
    public const STATUS_REJECTED = 'rejected';

    // Status SPJ (biaya rampung). null diperlakukan sebagai draf.
    public const SPJ_DRAFT = 'draft';
    public const SPJ_SUBMITTED = 'submitted';
    public const SPJ_APPROVED = 'approved';
    public const SPJ_REVISION = 'revision';

    protected $fillable = [
        'package_id',
        'tipe_perjalanan',
        'dasar_pelaksanaan',
        'maksud_perjalanan',
        'tempat_tujuan',
        'tanggal_berangkat',
        'tanggal_kembali',
        'tanggal_surat',
        'status',
        'catatan_review',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'created_by',
        'spj_status',
        'spj_catatan',
        'spj_submitted_at',
        'spj_reviewed_at',
        'spj_reviewed_by',
    ];

    protected $casts = [
        'tanggal_berangkat' => 'date',
        'tanggal_kembali' => 'date',
        'tanggal_surat' => 'date',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'spj_submitted_at' => 'datetime',
        'spj_reviewed_at' => 'datetime',
    ];

    /**
     * Metadata tampilan status pengajuan SPPD.
     *
     * @return array{label:string,badge:string,dot:string,icon:string}
     */
    public function statusMeta(): array
    {
        return [
            self::STATUS_DRAFT => [
                'label' => 'Draf', 'icon' => 'file-pen',
                'badge' => 'bg-slate-100 text-slate-600 border-slate-200', 'dot' => 'bg-slate-400',
            ],
            self::STATUS_SUBMITTED => [
                'label' => 'Diajukan', 'icon' => 'send',
                'badge' => 'bg-blue-50 text-blue-700 border-blue-200', 'dot' => 'bg-blue-500',
            ],
            self::STATUS_REVISION => [
                'label' => 'Perlu Revisi', 'icon' => 'file-warning',
                'badge' => 'bg-amber-50 text-amber-700 border-amber-200', 'dot' => 'bg-amber-500',
            ],
            self::STATUS_APPROVED => [
                'label' => 'Disetujui', 'icon' => 'check-circle-2',
                'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'dot' => 'bg-emerald-500',
            ],
            self::STATUS_REJECTED => [
                'label' => 'Ditolak', 'icon' => 'x-circle',
                'badge' => 'bg-rose-50 text-rose-700 border-rose-200', 'dot' => 'bg-rose-500',
            ],
        ][$this->status] ?? [
            'label' => ucfirst((string) $this->status), 'icon' => 'circle',
            'badge' => 'bg-slate-100 text-slate-600 border-slate-200', 'dot' => 'bg-slate-400',
        ];
    }

    public function isEditableBySubmitter(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REVISION], true);
    }

    /** Status SPJ efektif (null diperlakukan sebagai draf). */
    public function spjStatus(): string
    {
        return $this->spj_status ?: self::SPJ_DRAFT;
    }

    /** SPJ dapat diisi/diubah saat masih draf atau perlu revisi. */
    public function isSpjEditable(): bool
    {
        return in_array($this->spjStatus(), [self::SPJ_DRAFT, self::SPJ_REVISION], true);
    }

    /**
     * Metadata tampilan status SPJ.
     *
     * @return array{label:string,badge:string,icon:string}
     */
    public function spjStatusMeta(): array
    {
        return [
            self::SPJ_DRAFT => [
                'label' => 'Draf', 'icon' => 'file-pen',
                'badge' => 'bg-slate-100 text-slate-600 border-slate-200',
            ],
            self::SPJ_SUBMITTED => [
                'label' => 'Diajukan', 'icon' => 'send',
                'badge' => 'bg-blue-50 text-blue-700 border-blue-200',
            ],
            self::SPJ_REVISION => [
                'label' => 'Perlu Revisi', 'icon' => 'file-warning',
                'badge' => 'bg-amber-50 text-amber-700 border-amber-200',
            ],
            self::SPJ_APPROVED => [
                'label' => 'Disetujui', 'icon' => 'check-circle-2',
                'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            ],
        ][$this->spjStatus()];
    }

    /**
     * Cari bentrok jadwal: pegawai yang sudah terdaftar pada SPPD lain
     * yang rentang tanggalnya beririsan. SPPD ditolak tidak dihitung.
     *
     * @param  array<int>  $employeeIds
     * @return array<int, array{nama:string, tujuan:string, mulai:string, selesai:string}>
     */
    public static function bentrokJadwal(
        array $employeeIds,
        string $tanggalBerangkat,
        string $tanggalKembali,
        ?int $exceptTravelOrderId = null
    ): array {
        if (empty($employeeIds)) {
            return [];
        }

        $personnels = TravelPersonnel::query()
            ->whereIn('employee_id', $employeeIds)
            ->whereHas('travelOrder', function ($q) use ($tanggalBerangkat, $tanggalKembali, $exceptTravelOrderId) {
                $q->where('status', '!=', self::STATUS_REJECTED)
                    ->whereDate('tanggal_berangkat', '<=', $tanggalKembali)
                    ->whereDate('tanggal_kembali', '>=', $tanggalBerangkat)
                    ->when($exceptTravelOrderId, fn ($sub) => $sub->where('id', '!=', $exceptTravelOrderId));
            })
            ->with(['employee:id,nama', 'travelOrder:id,tempat_tujuan,tanggal_berangkat,tanggal_kembali'])
            ->get();

        return $personnels->map(fn ($p) => [
            'nama' => $p->employee?->nama ?? 'Pegawai',
            'tujuan' => $p->travelOrder?->tempat_tujuan ?? '-',
            'mulai' => $p->travelOrder?->tanggal_berangkat?->locale('id')->translatedFormat('d M Y') ?? '-',
            'selesai' => $p->travelOrder?->tanggal_kembali?->locale('id')->translatedFormat('d M Y') ?? '-',
        ])->values()->all();
    }

    /** Pesan validasi ringkas dari hasil bentrokJadwal(). */
    public static function pesanBentrok(array $bentrok): string
    {
        $rincian = collect($bentrok)
            ->map(fn ($b) => sprintf('%s (sudah SPPD ke %s, %s s.d. %s)', $b['nama'], $b['tujuan'], $b['mulai'], $b['selesai']))
            ->implode('; ');

        return 'Bentrok jadwal perjalanan dinas: ' . $rincian . '.';
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function report()
    {
        return $this->hasOne(TravelReport::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function spjReviewer()
    {
        return $this->belongsTo(User::class, 'spj_reviewed_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function personnels()
    {
        // Peserta pertama (urutan terkecil) adalah ketua rombongan / "Kepada" pada dokumen.
        return $this->hasMany(TravelPersonnel::class, 'travel_order_id')
            ->orderBy('urutan')
            ->orderBy('id');
    }
}
