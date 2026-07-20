<?php

namespace App\Http\Controllers\Kabid;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\ProcurementPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DikecualikanController extends Controller
{
    /**
     * Daftar pengadaan dikecualikan untuk Kabid.
     * Berbeda dengan penyedia: tanpa tahapan workflow — yang dipantau adalah
     * jenis (dalam/luar sistem), kelengkapan dokumen, dan realisasi.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Package::class);

        $typeFilter = $request->input('jenis'); // di_dalam_sistem | di_luar_sistem

        $base = ProcurementPackage::query()
            ->whereNotNull('dikecualikan_type')
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->whereHas('package', fn ($p) => $p
                    ->where('nama_paket', 'like', "%{$search}%")
                    ->orWhere('id_rup', 'like', "%{$search}%"));
            });

        // Statistik per jenis + kelengkapan dokumen (mengikuti pencarian).
        $stats = [
            'all'             => ['count' => 0, 'total' => 0, 'realisasi' => 0],
            'di_dalam_sistem' => ['count' => 0, 'total' => 0, 'realisasi' => 0],
            'di_luar_sistem'  => ['count' => 0, 'total' => 0, 'realisasi' => 0],
            'dokumen'         => ['lengkap' => 0, 'count' => 0],
        ];

        (clone $base)
            ->with('package:id,pagu')
            ->withCount('externalRecords')
            ->withSum('externalRecords as realisasi_sum', 'nilai_kontrak')
            ->get()
            ->each(function (ProcurementPackage $pp) use (&$stats) {
                $jenis = $pp->dikecualikan_type;
                $pagu = (float) ($pp->package->pagu ?? 0);
                $realisasi = (float) ($pp->realisasi_sum ?? 0);

                $stats['all']['count']++;
                $stats['all']['total'] += $pagu;
                $stats['all']['realisasi'] += $realisasi;

                if (isset($stats[$jenis])) {
                    $stats[$jenis]['count']++;
                    $stats[$jenis]['total'] += $pagu;
                    $stats[$jenis]['realisasi'] += $realisasi;
                }

                $stats['dokumen']['count']++;
                if (($pp->external_records_count ?? 0) > 0) {
                    $stats['dokumen']['lengkap']++;
                }
            });

        $procurementPackages = (clone $base)
            ->with('package.subActivity')
            ->withCount('externalRecords')
            ->withSum('externalRecords as realisasi_sum', 'nilai_kontrak')
            ->when($typeFilter, fn ($q) => $q->where('dikecualikan_type', $typeFilter))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('kabid.dikecualikan.index', compact(
            'procurementPackages', 'stats', 'typeFilter'
        ));
    }
}
