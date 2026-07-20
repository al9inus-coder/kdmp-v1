<?php

namespace App\Support;

use App\Models\Package;
use App\Models\ProcurementPackage;
use App\Models\ProcurementPayment;
use App\Models\ProcurementProcess;
use App\Models\TravelOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Registry objek bisnis yang boleh dikoreksi lewat Data Correction Center.
 *
 * User tidak pernah melihat nama tabel — setiap entri di sini adalah objek
 * bisnis (Paket Pengadaan, Perjalanan Dinas, dst.) dengan whitelist field
 * yang boleh dikoreksi. Field workflow/approval/audit sengaja tidak pernah
 * didaftarkan di sini.
 */
class DataCorrectionRegistry
{
    public static function types(): array
    {
        return [
            'paket-pengadaan' => [
                'label'  => 'Paket Pengadaan',
                'icon'   => 'package',
                'iconBg' => 'bg-indigo-50 text-indigo-600',
                'chip'   => 'bg-indigo-50 text-indigo-700',
                'model'  => Package::class,
                'with'   => ['procurementPackage', 'fiscalYear', 'program', 'approver'],
                'search' => function (Builder $q, string $term) {
                    $q->where(fn ($qq) => $qq
                        ->where('nama_paket', 'like', "%{$term}%")
                        ->orWhere('id_rup', 'like', "%{$term}%"));
                },
                'year' => fn (Builder $q, $tahun) => $q->whereHas('fiscalYear', fn ($f) => $f->where('tahun', $tahun)),
                'statuses' => ProcurementPackage::getWorkflowStatuses(),
                'status'   => fn (Builder $q, string $s) => $q->whereHas('procurementPackage', fn ($p) => $p->where('workflow_status', $s)),
                'title'    => fn (Model $o) => $o->nama_paket ?: '(Paket tanpa nama)',
                'subtitle' => fn (Model $o) => 'ID RUP: ' . ($o->id_rup ?: '-')
                    . ($o->fiscalYear ? ' · TA ' . $o->fiscalYear->tahun : ''),
                'statusLabel' => function (Model $o) {
                    if ($o->procurementPackage) {
                        return ProcurementPackage::getWorkflowStatuses()[$o->procurementPackage->workflow_status] ?? ucfirst((string) $o->procurementPackage->workflow_status);
                    }
                    return [
                        'draft' => 'Draft',
                        'submitted' => 'Menunggu Review',
                        'needs_review' => 'Perlu Review',
                        'approved' => 'Disetujui',
                    ][$o->status] ?? ucfirst(str_replace('_', ' ', (string) $o->status));
                },
                'approval' => fn (Model $o) => $o->approved_at
                    ? ['label' => 'Disetujui', 'date' => $o->approved_at, 'by' => $o->approver?->name]
                    : null,
                'fields' => [
                    'id_rup' => ['label' => 'ID RUP', 'type' => 'text', 'relation' => null, 'column' => 'id_rup'],
                    'nama_paket' => ['label' => 'Nama Paket', 'type' => 'textarea', 'relation' => null, 'column' => 'nama_paket'],
                    'nama_ppk' => ['label' => 'Nama PPK', 'type' => 'text', 'relation' => 'procurementPackage', 'column' => 'nama_ppk'],
                    'nip_ppk' => ['label' => 'NIP PPK', 'type' => 'text', 'relation' => 'procurementPackage', 'column' => 'nip_ppk'],
                    'garansi_nilai' => ['label' => 'Masa Garansi (Nilai)', 'type' => 'text', 'relation' => 'procurementPackage', 'column' => 'garansi_nilai'],
                    'garansi_satuan' => ['label' => 'Masa Garansi (Satuan)', 'type' => 'text', 'relation' => 'procurementPackage', 'column' => 'garansi_satuan'],
                    'layanan_purna_jual' => ['label' => 'Layanan Purna Jual', 'type' => 'textarea', 'relation' => 'procurementPackage', 'column' => 'layanan_purna_jual'],
                    'tanggal_barang_diterima' => ['label' => 'Tanggal Barang Diterima', 'type' => 'date', 'relation' => 'procurementPackage', 'column' => 'tanggal_barang_diterima'],
                ],
            ],

            'perjalanan-dinas' => [
                'label'  => 'Perjalanan Dinas',
                'icon'   => 'plane',
                'iconBg' => 'bg-sky-50 text-sky-600',
                'chip'   => 'bg-sky-50 text-sky-700',
                'model'  => TravelOrder::class,
                'with'   => ['package.fiscalYear'],
                'search' => function (Builder $q, string $term) {
                    $q->where(fn ($qq) => $qq
                        ->where('tempat_tujuan', 'like', "%{$term}%")
                        ->orWhere('maksud_perjalanan', 'like', "%{$term}%")
                        ->orWhere('dasar_pelaksanaan', 'like', "%{$term}%"));
                },
                'year' => fn (Builder $q, $tahun) => $q->whereHas('package.fiscalYear', fn ($f) => $f->where('tahun', $tahun)),
                'statuses' => [
                    TravelOrder::STATUS_DRAFT => 'Draf',
                    TravelOrder::STATUS_SUBMITTED => 'Diajukan',
                    TravelOrder::STATUS_APPROVED => 'Disetujui',
                    TravelOrder::STATUS_REVISION => 'Perlu Revisi',
                    TravelOrder::STATUS_REJECTED => 'Ditolak',
                ],
                'status'   => fn (Builder $q, string $s) => $q->where('status', $s),
                'title'    => fn (Model $o) => 'SPD ' . ($o->tempat_tujuan ?: '-')
                    . ($o->tanggal_berangkat ? ' — ' . $o->tanggal_berangkat->translatedFormat('d M Y') : ''),
                'subtitle' => fn (Model $o) => \Illuminate\Support\Str::limit($o->maksud_perjalanan ?: '-', 90),
                'statusLabel' => fn (Model $o) => $o->statusMeta()['label'],
                'approval' => fn (Model $o) => $o->status === TravelOrder::STATUS_APPROVED && $o->reviewed_at
                    ? ['label' => 'Disetujui', 'date' => $o->reviewed_at, 'by' => null]
                    : null,
                'fields' => [
                    'tempat_tujuan' => ['label' => 'Tempat Tujuan', 'type' => 'text', 'relation' => null, 'column' => 'tempat_tujuan'],
                    'maksud_perjalanan' => ['label' => 'Maksud Perjalanan', 'type' => 'textarea', 'relation' => null, 'column' => 'maksud_perjalanan'],
                    'dasar_pelaksanaan' => ['label' => 'Dasar Pelaksanaan / Nomor Surat', 'type' => 'textarea', 'relation' => null, 'column' => 'dasar_pelaksanaan'],
                    'tanggal_surat' => ['label' => 'Tanggal Surat', 'type' => 'date', 'relation' => null, 'column' => 'tanggal_surat'],
                ],
            ],

            'penyedia' => [
                'label'  => 'Penyedia',
                'icon'   => 'store',
                'iconBg' => 'bg-amber-50 text-amber-600',
                'chip'   => 'bg-amber-50 text-amber-700',
                'model'  => ProcurementProcess::class,
                'with'   => ['procurementPackage.package.fiscalYear'],
                'search' => function (Builder $q, string $term) {
                    $q->where(fn ($qq) => $qq
                        ->where('nama_penyedia', 'like', "%{$term}%")
                        ->orWhere('nomor_surat_pesanan', 'like', "%{$term}%")
                        ->orWhereHas('procurementPackage.package', fn ($p) => $p->where('nama_paket', 'like', "%{$term}%")));
                },
                'year' => fn (Builder $q, $tahun) => $q->whereHas('procurementPackage.package.fiscalYear', fn ($f) => $f->where('tahun', $tahun)),
                'statuses' => [],
                'status'   => null,
                'title'    => fn (Model $o) => $o->nama_penyedia ?: '(Penyedia belum diisi)',
                'subtitle' => fn (Model $o) => 'Paket: ' . ($o->procurementPackage?->package?->nama_paket ?: '-'),
                'statusLabel' => fn (Model $o) => ProcurementPackage::getWorkflowStatuses()[$o->procurementPackage?->workflow_status] ?? '-',
                'approval' => fn (Model $o) => null,
                'fields' => [
                    'nama_penyedia' => ['label' => 'Nama Penyedia', 'type' => 'text', 'relation' => null, 'column' => 'nama_penyedia'],
                    'alamat_penyedia' => ['label' => 'Alamat Penyedia', 'type' => 'textarea', 'relation' => null, 'column' => 'alamat_penyedia'],
                    'npwp_penyedia' => ['label' => 'NPWP Penyedia', 'type' => 'text', 'relation' => null, 'column' => 'npwp_penyedia'],
                    'nomor_surat_pesanan' => ['label' => 'Nomor Surat Pesanan', 'type' => 'text', 'relation' => null, 'column' => 'nomor_surat_pesanan'],
                    'tanggal_surat_pesanan' => ['label' => 'Tanggal Surat Pesanan', 'type' => 'date', 'relation' => null, 'column' => 'tanggal_surat_pesanan'],
                    'nama_bank' => ['label' => 'Nama Bank', 'type' => 'text', 'relation' => null, 'column' => 'nama_bank'],
                    'nomor_rekening' => ['label' => 'Nomor Rekening', 'type' => 'text', 'relation' => null, 'column' => 'nomor_rekening'],
                ],
            ],

            'pembayaran' => [
                'label'  => 'Pembayaran',
                'icon'   => 'banknote',
                'iconBg' => 'bg-emerald-50 text-emerald-600',
                'chip'   => 'bg-emerald-50 text-emerald-700',
                'model'  => ProcurementPayment::class,
                'with'   => ['procurementPackage.package.fiscalYear'],
                'search' => function (Builder $q, string $term) {
                    $q->where(fn ($qq) => $qq
                        ->where('nomor_bast', 'like', "%{$term}%")
                        ->orWhere('nomor_invoice', 'like', "%{$term}%")
                        ->orWhere('nomor_kwitansi', 'like', "%{$term}%")
                        ->orWhereHas('procurementPackage.package', fn ($p) => $p->where('nama_paket', 'like', "%{$term}%")));
                },
                'year' => fn (Builder $q, $tahun) => $q->whereHas('procurementPackage.package.fiscalYear', fn ($f) => $f->where('tahun', $tahun)),
                'statuses' => [],
                'status'   => null,
                'title'    => fn (Model $o) => 'Pembayaran — ' . ($o->procurementPackage?->package?->nama_paket ?: '-'),
                'subtitle' => fn (Model $o) => 'BAST: ' . ($o->nomor_bast ?: '-') . ' · Kwitansi: ' . ($o->nomor_kwitansi ?: '-'),
                'statusLabel' => fn (Model $o) => ProcurementPackage::getWorkflowStatuses()[$o->procurementPackage?->workflow_status] ?? '-',
                'approval' => fn (Model $o) => null,
                'fields' => [
                    'nomor_bast' => ['label' => 'Nomor BAST', 'type' => 'text', 'relation' => null, 'column' => 'nomor_bast'],
                    'tanggal_bast' => ['label' => 'Tanggal BAST', 'type' => 'date', 'relation' => null, 'column' => 'tanggal_bast'],
                    'nomor_invoice' => ['label' => 'Nomor Invoice', 'type' => 'text', 'relation' => null, 'column' => 'nomor_invoice'],
                    'nomor_bap' => ['label' => 'Nomor BAP', 'type' => 'text', 'relation' => null, 'column' => 'nomor_bap'],
                    'nomor_kwitansi' => ['label' => 'Nomor Kwitansi', 'type' => 'text', 'relation' => null, 'column' => 'nomor_kwitansi'],
                    'nama_pptk' => ['label' => 'Nama PPTK', 'type' => 'text', 'relation' => null, 'column' => 'nama_pptk'],
                    'nip_pptk' => ['label' => 'NIP PPTK', 'type' => 'text', 'relation' => null, 'column' => 'nip_pptk'],
                ],
            ],
        ];
    }

    public static function type(string $key): array
    {
        $types = self::types();
        abort_unless(isset($types[$key]), 404);

        return $types[$key];
    }

    public static function resolveObject(string $key, int $id): Model
    {
        $def = self::type($key);

        return $def['model']::with($def['with'])->findOrFail($id);
    }

    /** Model tempat kolom field berada (objek itu sendiri atau relasinya). */
    public static function resolveTarget(Model $object, array $field): ?Model
    {
        if (empty($field['relation'])) {
            return $object;
        }

        return $object->{$field['relation']};
    }

    /** Nilai kolom saat ini, dinormalisasi ke string (tanggal → Y-m-d). */
    public static function currentValue(Model $target, array $field): ?string
    {
        $raw = $target->{$field['column']};

        if ($raw === null || $raw === '') {
            return null;
        }

        if (($field['type'] ?? 'text') === 'date') {
            return Carbon::parse($raw)->format('Y-m-d');
        }

        return (string) $raw;
    }

    /** Format nilai untuk ditampilkan ke user. */
    public static function displayValue(?string $value, array $field): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        if (($field['type'] ?? 'text') === 'date') {
            return Carbon::parse($value)->translatedFormat('d F Y');
        }

        return $value;
    }
}
