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
    public function index(Request $request)
    {
        $tree = [];

        // 1. KATEGORI SPD (Dari TravelOrder)
        $orders = TravelOrder::query()
            ->whereNotNull('created_by')
            ->where('status', TravelOrder::STATUS_APPROVED)
            ->with(['package.fiscalYear', 'personnels.employee', 'report'])
            ->orderByDesc('tanggal_berangkat')
            ->get();

        $rolePrefix = auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Super Admin') ? 'admin.' : (auth()->user()->hasRole('Kabid') ? 'kabid.' : 'staf.');

        // Setiap perjalanan dinas jadi satu folder; di dalamnya dokumen-dokumennya.
        foreach ($orders as $to) {
            $package = $to->package;
            if (!$package) continue;

            $year = $package->fiscalYear?->tahun ?? $to->tanggal_berangkat?->format('Y') ?? 'Lainnya';

            // Nama folder perjalanan dinas: tujuan + rentang tanggal (disertai #id agar unik).
            $tglBerangkat = $to->tanggal_berangkat?->locale('id')->translatedFormat('d M Y');
            $tglKembali = $to->tanggal_kembali?->locale('id')->translatedFormat('d M Y');
            $periode = $tglBerangkat && $tglKembali && $tglBerangkat !== $tglKembali
                ? $tglBerangkat . ' – ' . $tglKembali
                : ($tglBerangkat ?? '-');
            $folder = trim(($to->tempat_tujuan ?: 'Perjalanan dinas') . ' · ' . $periode) . ' · #' . $to->id;

            $isLuarDaerah = in_array(strtolower($to->tipe_perjalanan), ['luar daerah', 'luar_daerah'], true);
            $spjApproved = $to->spjStatus() === TravelOrder::SPJ_APPROVED;

            $pushDoc = function (string $label, string $desc, string $url, string $action) use (&$tree, $year, $folder): void {
                $tree[$year]['SPD'][$folder][] = compact('label', 'url', 'action') + ['sub' => $desc];
            };

            $pushDoc('SPPD', 'Surat Perintah Perjalanan Dinas', route($rolePrefix . 'packages.travel-orders.print-html', [$package, $to, 'sppd']), 'tab');

            if ($isLuarDaerah) {
                $pushDoc('Surat Permohonan', 'Permohonan ke Bupati', route($rolePrefix . 'packages.travel-orders.export-word', [$package, $to, 'permohonan-bupati']), 'download');
            }
            $pushDoc('Surat Tugas', 'Surat tugas perjalanan dinas', route($rolePrefix . 'packages.travel-orders.export-word', [$package, $to, $isLuarDaerah ? 'surat-tugas-bupati' : 'surat-tugas-kadis']), 'download');

            if ($spjApproved) {
                $pushDoc('Kwitansi', 'Kwitansi per pelaksana', route($rolePrefix . 'packages.travel-orders.print-kuitansi', [$package, $to]), 'popup');
                $pushDoc('Pengeluaran Riil', 'Daftar pengeluaran riil', route($rolePrefix . 'packages.travel-orders.print-pengeluaran-riil', [$package, $to]), 'popup');
            }

            if ($to->report) {
                $pushDoc('Laporan Perjalanan Dinas', 'Laporan hasil perjalanan', route($rolePrefix . 'packages.travel-orders.print-laporan', [$package, $to]), 'popup');
            }
        }

        // Dokumen pengadaan (spek, referensi harga, persiapan, proses, pembayaran)
        // dari satu ProcurementPackage — dipakai Penyedia & Dikecualikan (di dalam sistem).
        $collectProcurementDocs = function ($pp, string $sub) use ($rolePrefix): array {
            $package = $pp->package;
            $docs = [];
            if ($pp->technicalSpecification) {
                $docs[] = ['label' => 'Spesifikasi Teknis', 'sub' => $sub, 'url' => route($rolePrefix . 'technical-specifications.print', $pp->technicalSpecification), 'action' => 'popup'];
            }
            if ($pp->priceReferences && $pp->priceReferences->count() > 0) {
                $docs[] = ['label' => 'Referensi Harga', 'sub' => $sub, 'url' => route($rolePrefix . 'procurement-packages.price-references.print', $package), 'action' => 'popup'];
            }
            if ($pp->procurementRequest) {
                $docs[] = ['label' => 'Form Persiapan Pengadaan', 'sub' => $sub, 'url' => route($rolePrefix . 'procurement-packages.procurement-request.print', $package), 'action' => 'popup'];
            }
            if ($pp->procurementProcess) {
                $docs[] = ['label' => 'Dokumen Proses Pengadaan (SSUK, SSKK, dll)', 'sub' => $sub, 'url' => route($rolePrefix . 'procurement-packages.procurement-process.print-document', $package), 'action' => 'popup'];
            }
            if ($pp->payment) {
                $docs[] = ['label' => 'Dokumen Pembayaran (BAP, Kwitansi, Ringkasan Kontrak)', 'sub' => $sub, 'url' => route($rolePrefix . 'procurement-packages.payment.print-document', $package), 'action' => 'popup'];
            }
            return $docs;
        };

        $procurementWith = [
            'package.fiscalYear', 'procurementRequest', 'procurementProcess',
            'payment', 'technicalSpecification', 'priceReferences',
        ];

        // 2. KATEGORI PBJ -> Penyedia
        $procurements = \App\Models\ProcurementPackage::with($procurementWith)
            ->whereNull('dikecualikan_type')
            ->whereHas('package', fn ($q) => $q->where('jenis_pengadaan', 'not like', '%swakelola%'))
            ->where('workflow_status', '!=', \App\Models\ProcurementPackage::WORKFLOW_DRAFT)
            ->get();

        foreach ($procurements as $pp) {
            if (!$pp->package) continue;
            $year = $pp->package->fiscalYear?->tahun ?? 'Lainnya';
            $folderName = Str::limit($pp->package->nama_paket, 80);
            $tree[$year]['PBJ']['Penyedia'][$folderName] = $collectProcurementDocs($pp, 'Penyedia · ' . ($pp->package->jenis_pengadaan ?? ''));
        }

        // 3. KATEGORI PBJ -> Dikecualikan — dokumen berupa kwitansi (external records)
        $dikecualikan = \App\Models\ProcurementPackage::with(['package.fiscalYear', 'externalRecords'])
            ->whereNotNull('dikecualikan_type')
            ->get();

        foreach ($dikecualikan as $pp) {
            if (!$pp->package) continue;
            $year = $pp->package->fiscalYear?->tahun ?? 'Lainnya';
            $folderName = Str::limit($pp->package->nama_paket, 80);
            $typeLabel = $pp->dikecualikan_type === 'di_luar_sistem' ? 'Di luar sistem' : 'Di dalam sistem';

            $tree[$year]['PBJ']['Dikecualikan'][$folderName] = $pp->externalRecords
                ->sortByDesc('kwitansi_tgl')
                ->map(function ($rec) use ($pp, $typeLabel, $rolePrefix) {
                    $noKwitansi = $rec->kwitansi_no ?: ('#' . $rec->id);
                    $tgl = $rec->kwitansi_tgl ? \Illuminate\Support\Carbon::parse($rec->kwitansi_tgl)->locale('id')->translatedFormat('d M Y') : null;
                    $nilai = $rec->nilai_kontrak ? 'Rp ' . number_format((float) $rec->nilai_kontrak, 0, ',', '.') : null;
                    $sub = collect([$typeLabel, $tgl, $nilai])->filter()->implode(' · ');

                    return [
                        'label' => 'Kwitansi ' . $noKwitansi,
                        'sub' => $sub,
                        'url' => route($rolePrefix . 'procurement-external-records.print', [$pp, $rec]),
                        'action' => 'popup',
                    ];
                })
                ->values()
                ->all();
        }

        // 4. KATEGORI PBJ -> Swakelola (dokumen lembur per bulan yang sudah dikunci)
        $bulanNama = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];

        $swakelola = \App\Models\Package::where('jenis_pengadaan', 'Swakelola')
            ->whereHas('overtimes', fn ($q) => $q->where('is_locked', true))
            ->with([
                'fiscalYear',
                'overtimes' => fn ($q) => $q->where('is_locked', true)->orderBy('tahun')->orderBy('bulan'),
            ])
            ->get();

        foreach ($swakelola as $package) {
            $year = $package->fiscalYear?->tahun ?? 'Lainnya';
            $folderName = Str::limit($package->nama_paket, 80);

            foreach ($package->overtimes as $ot) {
                $bln = ($bulanNama[$ot->bulan] ?? ('Bulan ' . $ot->bulan)) . ' ' . $ot->tahun;
                $sub = 'Lembur · ' . $bln;
                $tree[$year]['PBJ']['Swakelola'][$folderName][] = ['label' => 'Rekap Lembur ' . $bln, 'sub' => $sub, 'url' => route($rolePrefix . 'packages.overtimes.print', [$package, $ot, 'rekap']), 'action' => 'tab'];
                $tree[$year]['PBJ']['Swakelola'][$folderName][] = ['label' => 'Tanda Terima ' . $bln, 'sub' => $sub, 'url' => route($rolePrefix . 'packages.overtimes.print', [$package, $ot, 'tanda_terima']), 'action' => 'tab'];
                $tree[$year]['PBJ']['Swakelola'][$folderName][] = ['label' => 'Kwitansi Lembur ' . $bln, 'sub' => $sub, 'url' => route($rolePrefix . 'packages.overtimes.print', [$package, $ot, 'kwitansi']), 'action' => 'tab'];
            }
        }

        // Pastikan ketiga subkategori PBJ selalu ada (tampil walau kosong).
        foreach (array_keys($tree) as $year) {
            if (!isset($tree[$year]['PBJ']['Penyedia'])) $tree[$year]['PBJ']['Penyedia'] = [];
            if (!isset($tree[$year]['PBJ']['Swakelola'])) $tree[$year]['PBJ']['Swakelola'] = [];
            if (!isset($tree[$year]['PBJ']['Dikecualikan'])) $tree[$year]['PBJ']['Dikecualikan'] = [];
        }

        krsort($tree);

        // Urutan subkategori PBJ tetap baku (Penyedia, Swakelola, Dikecualikan).
        // SPD kini berupa folder per perjalanan dinas (urut mengikuti tanggal, terbaru dulu).
        $pbjOrder = ['Penyedia', 'Swakelola', 'Dikecualikan'];

        // Link Google Drive per tahun (dari config/arsip.php).
        $gdriveLinks = config('arsip.gdrive_links', []);
        $gdriveDefault = config('arsip.gdrive_default');

        foreach ($tree as $year => &$categories) {
            if (isset($categories['PBJ'])) {
                $pbjSorted = [];
                foreach ($pbjOrder as $type) {
                    if (isset($categories['PBJ'][$type])) {
                        $pbjSorted[$type] = $categories['PBJ'][$type];
                    }
                }
                $categories['PBJ'] = $pbjSorted;
            }

            // Susun ulang: SPD, lalu PBJ, lalu pintasan Google Drive (bila ada).
            $ordered = [];
            if (isset($categories['SPD'])) $ordered['SPD'] = $categories['SPD'];
            if (isset($categories['PBJ'])) $ordered['PBJ'] = $categories['PBJ'];

            $driveUrl = $gdriveLinks[$year] ?? $gdriveLinks[(string) $year] ?? $gdriveDefault;
            if ($driveUrl) {
                // Ditandai key khusus "__gdrive__" — view membukanya sebagai link, bukan folder.
                $ordered['__gdrive__'] = $driveUrl;
            }

            if ($ordered) {
                $categories = $ordered;
            }
        }

        return view('arsip.index', compact('tree'));
    }
}
