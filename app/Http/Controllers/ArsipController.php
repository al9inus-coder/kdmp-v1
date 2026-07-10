<?php

namespace App\Http\Controllers;

use App\Models\TravelOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Arsip Dokumen: folder virtual berisi dokumen yang dihasilkan aplikasi
 * (SPPD, Surat Tugas, Kwitansi, Pengeluaran Riil, Laporan, dst).
 * Dokumen di-render on-the-fly dari route print/export yang sudah ada —
 * tidak ada file fisik yang disimpan.
 *
 * Struktur: Tahun Anggaran -> Jenis Dokumen -> dokumen.
 */
class ArsipController extends Controller
{
    private const TYPE_ORDER = [
        'SPPD',
        'Surat Permohonan',
        'Surat Tugas',
        'Kwitansi',
        'Pengeluaran Riil',
        'Laporan Perjalanan Dinas',
    ];

    public function index(Request $request)
    {
        // Dokumen baru ada setelah SPPD disetujui.
        $orders = TravelOrder::query()
            ->whereNotNull('created_by')
            ->where('status', TravelOrder::STATUS_APPROVED)
            ->with(['package.fiscalYear', 'personnels.employee', 'report'])
            ->orderByDesc('tanggal_berangkat')
            ->get();

        $tree = [];

        foreach ($orders as $to) {
            $package = $to->package;
            if (!$package) {
                continue;
            }

            $year = $package->fiscalYear?->tahun
                ?? $to->tanggal_berangkat?->format('Y')
                ?? 'Lainnya';

            $label = Str::limit($to->maksud_perjalanan ?: 'Perjalanan dinas', 80);
            $sub = collect([
                $to->tempat_tujuan,
                $to->tanggal_berangkat?->locale('id')->translatedFormat('d M Y'),
                $to->personnels->count() . ' pelaksana',
            ])->filter()->implode(' · ');

            $isLuarDaerah = in_array(strtolower($to->tipe_perjalanan), ['luar daerah', 'luar_daerah'], true);
            $spjApproved = $to->spjStatus() === TravelOrder::SPJ_APPROVED;

            $push = function (string $type, string $url, string $action) use (&$tree, $year, $label, $sub): void {
                $tree[$year][$type][] = compact('label', 'sub', 'url', 'action');
            };

            $push('SPPD', route('packages.travel-orders.print-html', [$package, $to, 'sppd']), 'tab');

            if ($isLuarDaerah) {
                $push('Surat Permohonan', route('packages.travel-orders.export-word', [$package, $to, 'permohonan-bupati']), 'download');
            }
            $push('Surat Tugas', route('packages.travel-orders.export-word', [$package, $to, $isLuarDaerah ? 'surat-tugas-bupati' : 'surat-tugas-kadis']), 'download');

            if ($spjApproved) {
                $push('Kwitansi', route('packages.travel-orders.print-kuitansi', [$package, $to]), 'popup');
                $push('Pengeluaran Riil', route('packages.travel-orders.print-pengeluaran-riil', [$package, $to]), 'popup');
            }

            if ($to->report) {
                $push('Laporan Perjalanan Dinas', route('packages.travel-orders.print-laporan', [$package, $to]), 'popup');
            }
        }

        // Tahun terbaru dulu; jenis dokumen mengikuti urutan baku.
        krsort($tree);
        $tree = collect($tree)->map(function (array $types) {
            return collect(self::TYPE_ORDER)
                ->filter(fn ($t) => isset($types[$t]))
                ->mapWithKeys(fn ($t) => [$t => $types[$t]])
                ->all();
        })->all();

        return view('arsip.index', compact('tree'));
    }
}
