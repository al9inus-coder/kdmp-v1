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
        $tree = [];

        $push = function (string $year, string $category, string $subcategory, string $label, string $sub, string $url, string $action) use (&$tree): void {
            $tree[$year][$category][$subcategory][] = compact('label', 'sub', 'url', 'action');
        };

        // 1. KATEGORI SPD (Dari TravelOrder)
        $orders = TravelOrder::query()
            ->whereNotNull('created_by')
            ->where('status', TravelOrder::STATUS_APPROVED)
            ->with(['package.fiscalYear', 'personnels.employee', 'report'])
            ->orderByDesc('tanggal_berangkat')
            ->get();

        foreach ($orders as $to) {
            $package = $to->package;
            if (!$package) continue;

            $year = $package->fiscalYear?->tahun ?? $to->tanggal_berangkat?->format('Y') ?? 'Lainnya';
            $label = Str::limit($to->maksud_perjalanan ?: 'Perjalanan dinas', 80);
            $sub = collect([
                $to->tempat_tujuan,
                $to->tanggal_berangkat?->locale('id')->translatedFormat('d M Y'),
                $to->personnels->count() . ' pelaksana',
            ])->filter()->implode(' · ');

            $isLuarDaerah = in_array(strtolower($to->tipe_perjalanan), ['luar daerah', 'luar_daerah'], true);
            $spjApproved = $to->spjStatus() === TravelOrder::SPJ_APPROVED;

            $push($year, 'SPD', 'SPPD', $label, $sub, route('packages.travel-orders.print-html', [$package, $to, 'sppd']), 'tab');

            if ($isLuarDaerah) {
                $push($year, 'SPD', 'Surat Permohonan', $label, $sub, route('packages.travel-orders.export-word', [$package, $to, 'permohonan-bupati']), 'download');
            }
            $push($year, 'SPD', 'Surat Tugas', $label, $sub, route('packages.travel-orders.export-word', [$package, $to, $isLuarDaerah ? 'surat-tugas-bupati' : 'surat-tugas-kadis']), 'download');

            if ($spjApproved) {
                $push($year, 'SPD', 'Kwitansi', $label, $sub, route('packages.travel-orders.print-kuitansi', [$package, $to]), 'popup');
                $push($year, 'SPD', 'Pengeluaran Riil', $label, $sub, route('packages.travel-orders.print-pengeluaran-riil', [$package, $to]), 'popup');
            }

            if ($to->report) {
                $push($year, 'SPD', 'Laporan Perjalanan Dinas', $label, $sub, route('packages.travel-orders.print-laporan', [$package, $to]), 'popup');
            }
        }

        // 2. KATEGORI PBJ -> Penyedia (Dari ProcurementPackage)
        $procurements = \App\Models\ProcurementPackage::with([
            'package.fiscalYear',
            'procurementRequest',
            'procurementProcess',
            'payment',
            'technicalSpecification',
            'priceReferences',
        ])
            ->whereNull('dikecualikan_type')
            ->whereHas('package', fn ($q) => $q->where('jenis_pengadaan', 'not like', '%swakelola%'))
            ->where('workflow_status', '!=', \App\Models\ProcurementPackage::WORKFLOW_DRAFT)
            ->get();

        foreach ($procurements as $pp) {
            $package = $pp->package;
            $year = $package->fiscalYear?->tahun ?? 'Lainnya';
            $labelPkg = Str::limit($package->nama_paket, 80);
            $subPkg = 'Penyedia · ' . ($package->jenis_pengadaan ?? '');
            
            // Nama Folder Paket
            $folderName = $labelPkg;

            // Inisialisasi empty array untuk memastikan terbaca sebagai folder di client
            if (!isset($tree[$year]['PBJ']['Penyedia'][$folderName])) {
                $tree[$year]['PBJ']['Penyedia'][$folderName] = [];
            }

            // Spesifikasi Teknis
            if ($pp->technicalSpecification) {
                $tree[$year]['PBJ']['Penyedia'][$folderName][] = [
                    'label' => 'Spesifikasi Teknis', 'sub' => $subPkg,
                    'url' => route('technical-specifications.print', $pp->technicalSpecification), 'action' => 'popup'
                ];
            }
            
            // Referensi Harga
            if ($pp->priceReferences && $pp->priceReferences->count() > 0) {
                $tree[$year]['PBJ']['Penyedia'][$folderName][] = [
                    'label' => 'Referensi Harga', 'sub' => $subPkg,
                    'url' => route('procurement-packages.price-references.print', $package), 'action' => 'popup'
                ];
            }

            // Form Persiapan Pengadaan (Surat Permohonan dll)
            if ($pp->procurementRequest) {
                $tree[$year]['PBJ']['Penyedia'][$folderName][] = [
                    'label' => 'Form Persiapan Pengadaan', 'sub' => $subPkg,
                    'url' => route('procurement-packages.procurement-request.print', $package), 'action' => 'popup'
                ];
            }

            // Proses Pengadaan (SSUK, SSKK, Surat Pesanan, dsb)
            if ($pp->procurementProcess) {
                $tree[$year]['PBJ']['Penyedia'][$folderName][] = [
                    'label' => 'Dokumen Proses Pengadaan (SSUK, SSKK, dll)', 'sub' => $subPkg,
                    'url' => route('procurement-packages.procurement-process.print-document', $package), 'action' => 'popup'
                ];
            }

            // Dokumen Pembayaran (BAP, Kwitansi, Ringkasan Kontrak)
            if ($pp->payment) {
                $tree[$year]['PBJ']['Penyedia'][$folderName][] = [
                    'label' => 'Dokumen Pembayaran (BAP, Kwitansi, Ringkasan Kontrak)', 'sub' => $subPkg,
                    'url' => route('procurement-payments.print-document', $package), 'action' => 'popup'
                ];
            }
        }

        // 3. KATEGORI PBJ -> Swakelola & Dikecualikan (kosong untuk sementara)
        // Kita deklarasikan empty array agar foldernya tetap ada
        foreach (array_keys($tree) as $year) {
            if (!isset($tree[$year]['PBJ']['Swakelola'])) $tree[$year]['PBJ']['Swakelola'] = [];
            if (!isset($tree[$year]['PBJ']['Dikecualikan'])) $tree[$year]['PBJ']['Dikecualikan'] = [];
        }

        krsort($tree);

        // Define urutan baku untuk SPD
        $spdOrder = [
            'SPPD',
            'Surat Permohonan',
            'Surat Tugas',
            'Kwitansi',
            'Pengeluaran Riil',
            'Laporan Perjalanan Dinas',
        ];

        // Define urutan baku untuk PBJ
        $pbjOrder = [
            'Penyedia',
            'Swakelola',
            'Dikecualikan'
        ];

        // Sorting Subkategori sesuai urutan baku
        foreach ($tree as $year => &$categories) {
            if (isset($categories['SPD'])) {
                $spdSorted = [];
                foreach ($spdOrder as $type) {
                    if (isset($categories['SPD'][$type])) {
                        $spdSorted[$type] = $categories['SPD'][$type];
                    }
                }
                $categories['SPD'] = $spdSorted;
            }

            if (isset($categories['PBJ'])) {
                $pbjSorted = [];
                foreach ($pbjOrder as $type) {
                    if (isset($categories['PBJ'][$type])) {
                        $pbjSorted[$type] = $categories['PBJ'][$type];
                    }
                }
                $categories['PBJ'] = $pbjSorted;
            }
        }

        return view('arsip.index', compact('tree'));
    }
}
