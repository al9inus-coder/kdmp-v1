<?php

namespace App\Http\Controllers;

use App\Models\FiscalYear;
use App\Models\Package;
use App\Models\ProcurementPackage;
use App\Models\TravelOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        if (auth()->check()) {
            if (auth()->user()->hasRole('Admin')) {
                return redirect()->route('dashboard.admin');
            } elseif (auth()->user()->hasRole('Kabid')) {
                return redirect()->route('dashboard.kabid');
            } elseif (auth()->user()->hasRole('Staff')) {
                return redirect()->route('dashboard.staf');
            }
        }
        
        // Default fallback
        return redirect()->route('dashboard.admin');
    }

    public function admin(Request $request)
    {
        $fiscalYears = FiscalYear::orderBy('tahun', 'desc')->get();
        
        $fiscalYearId = $request->get('fiscal_year_id');
        if (!$fiscalYearId) {
            $activeYear = $fiscalYears->where('is_active', true)->first();
            $fiscalYearId = $activeYear ? $activeYear->id : ($fiscalYears->first()->id ?? null);
        }

        // Base Query
        $packagesQuery = Package::where('fiscal_year_id', $fiscalYearId);
        
        // Metrics
        $totalPagu = $packagesQuery->sum('pagu');
        $totalPackages = $packagesQuery->count();
        
        // Procurement Packages in this fiscal year
        $procurementPackagesQuery = ProcurementPackage::whereHas('package', function($q) use ($fiscalYearId) {
            $q->where('fiscal_year_id', $fiscalYearId);
        });

        $completedCount = (clone $procurementPackagesQuery)
            ->where('workflow_status', ProcurementPackage::WORKFLOW_COMPLETED)
            ->count();
            
        // Calculate realized budget (sum of nilai_kontrak for COMPLETED processes)
        $realizedBudget = \App\Models\ProcurementProcess::whereHas('procurementPackage', function($q) use ($fiscalYearId) {
            $q->where('workflow_status', ProcurementPackage::WORKFLOW_COMPLETED)
              ->whereHas('package', function($p) use ($fiscalYearId) {
                  $p->where('fiscal_year_id', $fiscalYearId);
              });
        })->sum('nilai_kontrak');
        
        $absorptionPercentage = $totalPagu > 0 ? round(($realizedBudget / $totalPagu) * 100, 2) : 0;

        // Status Distribution
        $statusDistribution = (clone $procurementPackagesQuery)
            ->selectRaw('workflow_status, count(*) as total')
            ->groupBy('workflow_status')
            ->pluck('total', 'workflow_status')
            ->toArray();
            
        // Jenis Pengadaan Distribution
        $jenisPengadaanDistribution = (clone $packagesQuery)
            ->selectRaw('jenis_pengadaan, count(*) as total')
            ->groupBy('jenis_pengadaan')
            ->pluck('total', 'jenis_pengadaan')
            ->toArray();

        // Late Packages (Warning System)
        $latePackages = (clone $packagesQuery)
            ->where('pemilihan_mulai_bulan', '<=', now()->month)
            ->whereHas('procurementPackage', function($q) {
                $q->whereIn('workflow_status', [
                    ProcurementPackage::WORKFLOW_DRAFT
                ]);
            })
            ->with(['procurementPackage', 'activity', 'subActivity'])
            ->limit(10)
            ->get();
            
        // Recent Activities (latest updated procurement packages)
        $recentActivities = (clone $procurementPackagesQuery)
            ->with(['package'])
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.admin', compact(
            'fiscalYears', 'fiscalYearId', 'totalPagu', 'totalPackages', 'completedCount',
            'realizedBudget', 'absorptionPercentage', 'statusDistribution',
            'jenisPengadaanDistribution', 'latePackages', 'recentActivities'
        ));
    }

    public function kabid(Request $request)
    {
        $activeFiscalYear = FiscalYear::where('is_active', true)->first()
            ?? FiscalYear::orderBy('tahun', 'desc')->first();

        $baseQuery = Package::query();
        if ($activeFiscalYear) {
            $baseQuery->where('fiscal_year_id', $activeFiscalYear->id);
        }

        $totalPaket       = (clone $baseQuery)->count();
        $totalPagu        = (clone $baseQuery)->sum('pagu');
        $needsReviewCount = (clone $baseQuery)->where('status', 'needs_review')->count();
        $draftCount       = (clone $baseQuery)->where('status', 'draft')->count();
        $submittedCount   = (clone $baseQuery)->where('status', 'submitted')->count();
        $approvedCount    = (clone $baseQuery)->where('status', 'approved')->count();

        $submittedPagu = (clone $baseQuery)->where('status', 'submitted')->sum('pagu');

        // Paket menunggu persetujuan (yang paling lama diajukan tampil dulu)
        $pendingPackages = (clone $baseQuery)
            ->with(['subActivity', 'submitter'])
            ->where('status', 'submitted')
            ->orderBy('submitted_at')
            ->limit(8)
            ->get();

        // SPPD yang menunggu review kabid
        $pendingSppd = \App\Models\TravelOrder::with(['package.subActivity', 'creator', 'personnels.employee'])
            ->where('status', \App\Models\TravelOrder::STATUS_SUBMITTED)
            ->orderBy('submitted_at')
            ->limit(5)
            ->get();
        $pendingSppdCount = \App\Models\TravelOrder::where('status', \App\Models\TravelOrder::STATUS_SUBMITTED)->count();

        // SPJ (biaya rampung) yang menunggu review kabid: SPPD disetujui + SPJ diajukan.
        $pendingSpjQuery = \App\Models\TravelOrder::where('status', \App\Models\TravelOrder::STATUS_APPROVED)
            ->where('spj_status', \App\Models\TravelOrder::SPJ_SUBMITTED);
        $pendingSpj = (clone $pendingSpjQuery)
            ->with(['package.subActivity', 'creator', 'personnels.employee'])
            ->orderBy('spj_submitted_at')
            ->limit(5)
            ->get();
        $pendingSpjCount = (clone $pendingSpjQuery)->count();

        // Gabungan pengajuan perjalanan dinas (SPPD baru + SPJ) untuk satu kartu inbox.
        $pendingReview = $pendingSppd->map(fn ($to) => ['to' => $to, 'type' => 'sppd', 'time' => $to->submitted_at])
            ->concat($pendingSpj->map(fn ($to) => ['to' => $to, 'type' => 'spj', 'time' => $to->spj_submitted_at]))
            ->filter(fn ($r) => $r['time'])
            ->sortByDesc('time')
            ->take(5)
            ->values();
        $pendingReviewCount = $pendingSppdCount + $pendingSpjCount;

        // ── Anggaran: realisasi, sisa, serapan ─────────────────────────────
        $fiscalYearId = $activeFiscalYear?->id;

        $realisasiPenyedia = \App\Models\ProcurementProcess::whereHas('procurementPackage', function ($q) use ($fiscalYearId) {
            $q->where('workflow_status', ProcurementPackage::WORKFLOW_COMPLETED)
                ->when($fiscalYearId, fn ($qq) => $qq->whereHas('package', fn ($p) => $p->where('fiscal_year_id', $fiscalYearId)));
        })->sum('nilai_kontrak');

        $realisasiDikecualikan = \App\Models\ProcurementExternalRecord::whereHas('procurementPackage', function ($q) use ($fiscalYearId) {
            $q->whereNotNull('dikecualikan_type')
                ->when($fiscalYearId, fn ($qq) => $qq->whereHas('package', fn ($p) => $p->where('fiscal_year_id', $fiscalYearId)));
        })->sum('nilai_kontrak');

        $realisasi    = (float) $realisasiPenyedia + (float) $realisasiDikecualikan;
        $sisaAnggaran = (float) $totalPagu - $realisasi;
        $serapanPct   = $totalPagu > 0 ? round($realisasi / $totalPagu * 100, 1) : 0;

        // ── Proses pengadaan penyedia (distribusi tahapan workflow) ────────
        $workflowStats = [
            ProcurementPackage::WORKFLOW_DRAFT              => 0,
            ProcurementPackage::WORKFLOW_PROVIDER_SELECTION => 0,
            ProcurementPackage::WORKFLOW_EXECUTION          => 0,
            ProcurementPackage::WORKFLOW_PAYMENT_PROCESS    => 0,
            ProcurementPackage::WORKFLOW_COMPLETED          => 0,
        ];

        ProcurementPackage::whereNull('dikecualikan_type')
            ->whereHas('package', fn ($p) => $p
                ->where('jenis_pengadaan', 'not like', '%swakelola%')
                ->when($fiscalYearId, fn ($q) => $q->where('fiscal_year_id', $fiscalYearId)))
            ->pluck('workflow_status')
            ->each(function ($status) use (&$workflowStats) {
                $key = array_key_exists($status, $workflowStats) ? $status : ProcurementPackage::WORKFLOW_DRAFT;
                $workflowStats[$key]++;
            });

        // ── Pengingat ──────────────────────────────────────────────────────
        $reminders = [];

        $lateCount = (clone $baseQuery)
            ->where('pemilihan_mulai_bulan', '<=', now()->month)
            ->whereHas('procurementPackage', fn ($q) => $q->where('workflow_status', ProcurementPackage::WORKFLOW_DRAFT))
            ->count();
        if ($lateCount > 0) {
            $reminders[] = [
                'icon' => 'alarm-clock', 'color' => 'rose',
                'title' => $lateCount.' paket melewati jadwal pemilihan',
                'desc'  => 'Target bulan pemilihan sudah tiba, proses masih draft.',
                'url'   => route('kabid.penyedia.index', ['status' => 'draft']),
            ];
        }

        $noRoomCount = (clone $baseQuery)->where('status', 'approved')->whereDoesntHave('procurementPackage')->count();
        if ($noRoomCount > 0) {
            $reminders[] = [
                'icon' => 'door-closed', 'color' => 'amber',
                'title' => $noRoomCount.' paket disetujui belum punya ruang pengadaan',
                'desc'  => 'Buat ruang pengadaan dari halaman detail paket.',
                'url'   => route('kabid.packages.index', ['status' => 'approved']),
            ];
        }

        $staleSppdCount = \App\Models\TravelOrder::where('status', \App\Models\TravelOrder::STATUS_SUBMITTED)
            ->where('submitted_at', '<=', now()->subDays(3))
            ->count();
        if ($staleSppdCount > 0) {
            $reminders[] = [
                'icon' => 'plane', 'color' => 'sky',
                'title' => $staleSppdCount.' SPPD menunggu lebih dari 3 hari',
                'desc'  => 'Segera review agar rencana perjalanan tidak tertunda.',
                'url'   => route('kabid.sppd.index', ['status' => 'submitted']),
            ];
        }

        if ($pendingSpjCount > 0) {
            $reminders[] = [
                'icon' => 'receipt-text', 'color' => 'blue',
                'title' => $pendingSpjCount.' SPJ perjalanan dinas menunggu review',
                'desc'  => 'Staf mengajukan biaya rampung — setujui atau minta revisi.',
                'url'   => route('kabid.sppd.index', ['status' => 'spj_submitted']),
            ];
        }

        $dokKurangCount = ProcurementPackage::whereNotNull('dikecualikan_type')
            ->when($fiscalYearId, fn ($q) => $q->whereHas('package', fn ($p) => $p->where('fiscal_year_id', $fiscalYearId)))
            ->doesntHave('externalRecords')
            ->count();
        if ($dokKurangCount > 0) {
            $reminders[] = [
                'icon' => 'file-x', 'color' => 'violet',
                'title' => $dokKurangCount.' pengadaan dikecualikan tanpa dokumen',
                'desc'  => 'Dokumen SP/BAST/kwitansi belum tercatat.',
                'url'   => route('kabid.dikecualikan.index'),
            ];
        }

        // ── Monitoring Perjalanan Dinas (Swakelola Perjalanan Dinas) ────────
        $travelPackages = Package::where('fiscal_year_id', $fiscalYearId)
            ->where('status', 'approved')
            ->with(['subActivity', 'account', 'travelOrders.personnels'])
            ->get()
            ->filter(function ($pkg) {
                $jenisPengadaan = str($pkg->jenis_pengadaan ?? '')->lower();
                $accountName = str($pkg->account?->nama ?? '')->lower();
                return $jenisPengadaan->contains('swakelola') && $accountName->contains('perjalanan dinas');
            });

        $travelSubActivities = [];
        $totalTravelPagu = 0.0;
        $totalTravelRealisasi = 0.0;

        $colorsList = [
            ['hex' => '#3b82f6', 'dot' => 'bg-blue-500'],
            ['hex' => '#10b981', 'dot' => 'bg-emerald-500'],
            ['hex' => '#f59e0b', 'dot' => 'bg-amber-500'],
            ['hex' => '#f97316', 'dot' => 'bg-orange-500'],
            ['hex' => '#8b5cf6', 'dot' => 'bg-violet-500'],
            ['hex' => '#06b6d4', 'dot' => 'bg-cyan-500'],
            ['hex' => '#ec4899', 'dot' => 'bg-pink-500'],
            ['hex' => '#14b8a6', 'dot' => 'bg-teal-500'],
        ];

        $colorIdx = 0;
        foreach ($travelPackages as $pkg) {
            $subAct = $pkg->subActivity;
            if (!$subAct) continue;
            
            if (!isset($travelSubActivities[$subAct->id])) {
                $color = $colorsList[$colorIdx % count($colorsList)];
                $colorIdx++;

                $travelSubActivities[$subAct->id] = [
                    'id' => $subAct->id,
                    'kode' => $subAct->kode,
                    'nama' => $subAct->nama,
                    'pagu' => 0.0,
                    'realisasi' => 0.0,
                    'hex' => $color['hex'],
                    'dot' => $color['dot'],
                    'id_rup' => $pkg->id_rup,
                ];
            }
            
            $travelSubActivities[$subAct->id]['pagu'] += (float) $pkg->pagu;
            $totalTravelPagu += (float) $pkg->pagu;
            
            $realisasi = 0.0;
            foreach ($pkg->travelOrders ?? [] as $travelOrder) {
                if ($travelOrder->spjStatus() !== \App\Models\TravelOrder::SPJ_APPROVED) { continue; }
                foreach ($travelOrder->personnels ?? [] as $personnel) {
                    $realisasi += (float) $personnel->uang_harian
                        + (float) $personnel->biaya_penginapan
                        + (float) $personnel->biaya_representasi
                        + (float) $personnel->biaya_transport
                        + (float) ($personnel->biaya_taksi ?? 0);
                }
            }
            $travelSubActivities[$subAct->id]['realisasi'] += $realisasi;
            $totalTravelRealisasi += $realisasi;
        }

        // Sort by pagu descending
        usort($travelSubActivities, fn ($a, $b) => $b['pagu'] <=> $a['pagu']);

        // ── Riwayat aktivitas gabungan (persetujuan, SPPD, pengadaan) ──────
        $recentActivities = collect()
            ->concat((clone $baseQuery)
                ->where('status', 'approved')->whereNotNull('approved_at')
                ->orderByDesc('approved_at')->limit(5)->get()
                ->map(fn ($pkg) => [
                    'icon'  => 'check-circle', 'color' => 'emerald',
                    'title' => 'Paket disetujui: '.$pkg->nama_paket,
                    'desc'  => 'Rp '.number_format((float) $pkg->pagu, 0, ',', '.'),
                    'time'  => $pkg->approved_at,
                    'url'   => route('kabid.packages.show', $pkg),
                ]))
            ->concat(\App\Models\TravelOrder::with('package')
                ->whereNotNull('reviewed_at')
                ->orderByDesc('reviewed_at')->limit(5)->get()
                ->map(function ($to) {
                    $label = match ($to->status) {
                        \App\Models\TravelOrder::STATUS_APPROVED => 'disetujui',
                        \App\Models\TravelOrder::STATUS_REVISION => 'diminta revisi',
                        \App\Models\TravelOrder::STATUS_REJECTED => 'ditolak',
                        default => 'ditinjau',
                    };
                    return [
                        'icon'  => 'plane', 'color' => 'sky',
                        'title' => 'SPPD '.$label.': '.\Illuminate\Support\Str::limit($to->maksud_perjalanan, 45),
                        'desc'  => $to->tempat_tujuan,
                        'time'  => $to->reviewed_at ? \Illuminate\Support\Carbon::parse($to->reviewed_at) : null,
                        'url'   => $to->package ? route('kabid.packages.travel-orders.show', [$to->package, $to]) : '#',
                    ];
                }))
            ->concat(ProcurementPackage::with('package')
                ->where('workflow_status', '!=', ProcurementPackage::WORKFLOW_DRAFT)
                ->orderByDesc('updated_at')->limit(5)->get()
                ->map(fn ($pp) => [
                    'icon'  => 'briefcase', 'color' => 'indigo',
                    'title' => 'Pengadaan: '.($pp->package->nama_paket ?? 'Paket #'.$pp->id),
                    'desc'  => 'Tahap '.str_replace('_', ' ', (string) $pp->workflow_status),
                    'time'  => $pp->updated_at,
                    'url'   => $pp->package ? route('kabid.procurement-packages.show', $pp->package) : '#',
                ]))
            ->filter(fn ($activity) => $activity['time'])
            ->sortByDesc('time')
            ->take(7)
            ->values();

        return view('dashboard.kabid', compact(
            'activeFiscalYear',
            'totalPaket', 'totalPagu', 'realisasi', 'sisaAnggaran', 'serapanPct',
            'needsReviewCount', 'draftCount', 'submittedCount', 'approvedCount',
            'submittedPagu', 'pendingPackages', 'pendingSppd', 'pendingSppdCount',
            'pendingSpj', 'pendingSpjCount', 'pendingReview', 'pendingReviewCount',
            'workflowStats', 'reminders', 'recentActivities',
            'travelSubActivities', 'totalTravelPagu', 'totalTravelRealisasi'
        ));
    }

    public function staf(Request $request)
    {
        $user = auth()->user();

        // Ambil tahun fiskal aktif
        $activeFiscalYear = FiscalYear::where('is_active', true)->first()
            ?? FiscalYear::orderBy('tahun', 'desc')->first();

        // Statistik paket berdasarkan status
        $baseQuery = Package::query();
        if ($activeFiscalYear) {
            $baseQuery->where('fiscal_year_id', $activeFiscalYear->id);
        }

        $totalPaket      = (clone $baseQuery)->count();
        $draftCount      = (clone $baseQuery)->where('status', 'draft')->count();
        $needsReviewCount = (clone $baseQuery)->where('status', 'needs_review')->count();
        $submittedCount  = (clone $baseQuery)->where('status', 'submitted')->count();
        $approvedCount   = (clone $baseQuery)->where('status', 'approved')->count();

        // Paket needs_review (perlu dilengkapi)
        $needsReviewPackages = (clone $baseQuery)
            ->where('status', 'needs_review')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // ===== Perjalanan Dinas (SPPD) =====
        $sppdBase = TravelOrder::query()->whereNotNull('created_by');

        $sppdCounts = [
            'draft'     => (clone $sppdBase)->where('status', TravelOrder::STATUS_DRAFT)->count(),
            'submitted' => (clone $sppdBase)->where('status', TravelOrder::STATUS_SUBMITTED)->count(),
            'revision'  => (clone $sppdBase)->where('status', TravelOrder::STATUS_REVISION)->count(),
            'approved'  => (clone $sppdBase)->where('status', TravelOrder::STATUS_APPROVED)->count(),
        ];

        // SPJ perlu diproses: SPPD sudah disetujui tapi SPJ belum disetujui (draf/null/revisi).
        $spjPendingCount = (clone $sppdBase)
            ->where('status', TravelOrder::STATUS_APPROVED)
            ->where(fn ($q) => $q
                ->whereNull('spj_status')
                ->orWhereIn('spj_status', [TravelOrder::SPJ_DRAFT, TravelOrder::SPJ_REVISION]))
            ->count();

        // SPPD yang butuh tindakan staf: draf & perlu revisi.
        $sppdActionCount = $sppdCounts['draft'] + $sppdCounts['revision'];

        // Perjalanan dinas terbaru (5)
        $recentTravels = (clone $sppdBase)
            ->with(['personnels.employee', 'package.subActivity'])
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();

        // Agenda perjalanan: sedang berlangsung / akan datang (disetujui, belum lewat)
        $upcomingTravels = (clone $sppdBase)
            ->where('status', TravelOrder::STATUS_APPROVED)
            ->whereDate('tanggal_kembali', '>=', Carbon::today())
            ->with(['personnels.employee'])
            ->orderBy('tanggal_berangkat')
            ->limit(5)
            ->get();

        // Aktivitas terakhir user ini: impor RUP dan pengajuan paket
        $recentActivities = collect()
            ->concat(
                \App\Models\ImportBatch::where('created_by', $user->id)
                    ->orderByDesc('created_at')
                    ->limit(6)
                    ->get()
                    ->map(fn ($batch) => [
                        'icon'  => 'upload',
                        'color' => 'emerald',
                        'title' => 'Impor RUP: '.$batch->file_name,
                        'desc'  => $batch->success_rows.' berhasil, '.$batch->failed_rows.' gagal',
                        'time'  => $batch->created_at,
                        'url'   => route('staf.packages.import.show', $batch),
                    ])
            )
            ->concat(
                Package::where('submitted_by', $user->id)
                    ->whereNotNull('submitted_at')
                    ->orderByDesc('submitted_at')
                    ->limit(6)
                    ->get()
                    ->map(fn ($pkg) => [
                        'icon'  => 'send',
                        'color' => 'blue',
                        'title' => 'Mengajukan paket: '.$pkg->nama_paket,
                        'desc'  => 'Paket pengadaan',
                        'time'  => $pkg->submitted_at,
                        'url'   => route('staf.packages.show', $pkg),
                    ])
            )
            ->concat(
                (clone $sppdBase)
                    ->where('created_by', $user->id)
                    ->whereNotNull('submitted_at')
                    ->orderByDesc('submitted_at')
                    ->limit(6)
                    ->get()
                    ->map(fn ($to) => [
                        'icon'  => 'plane',
                        'color' => 'indigo',
                        'title' => 'Mengajukan SPPD: '.$to->tempat_tujuan,
                        'desc'  => $to->maksud_perjalanan ? \Illuminate\Support\Str::limit($to->maksud_perjalanan, 40) : 'Perjalanan dinas',
                        'time'  => $to->submitted_at,
                        'url'   => route('staf.packages.travel-orders.show', [$to->package_id, $to]),
                    ])
            )
            ->sortByDesc('time')
            ->take(6)
            ->values();

        return view('staf.dashboard', compact(
            'activeFiscalYear',
            'totalPaket',
            'draftCount', 'needsReviewCount', 'submittedCount', 'approvedCount',
            'needsReviewPackages', 'recentActivities',
            'sppdCounts', 'spjPendingCount', 'sppdActionCount',
            'recentTravels', 'upcomingTravels'
        ));
    }
}

