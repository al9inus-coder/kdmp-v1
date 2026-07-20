<?php

namespace App\Http\Controllers\Kabid;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\ProcurementPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PenyediaController extends Controller
{
    /**
     * Daftar pengadaan melalui penyedia — satu-satunya kategori yang
     * berjalan mengikuti tahapan workflow (persiapan → pemilihan → pelaksanaan → pembayaran).
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Package::class);

        $base = ProcurementPackage::query()
            ->whereNull('dikecualikan_type')
            ->whereHas('package', fn ($q) => $q->where('jenis_pengadaan', 'not like', '%swakelola%'))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->whereHas('package', fn ($p) => $p
                    ->where('nama_paket', 'like', "%{$search}%")
                    ->orWhere('id_rup', 'like', "%{$search}%"));
            })
            ->when($request->filled('program_id'), fn ($q) => $q
                ->whereHas('package', fn ($p) => $p->where('program_id', $request->program_id)));

        // Statistik per tahapan (mengikuti pencarian/program, tanpa filter status)
        $stats = [
            'draft'     => ['count' => 0, 'total' => 0],
            'persiapan' => ['count' => 0, 'total' => 0],
            'diproses'  => ['count' => 0, 'total' => 0],
            'selesai'   => ['count' => 0, 'total' => 0],
        ];

        (clone $base)->with('package:id,pagu')->get()->each(function (ProcurementPackage $pp) use (&$stats) {
            $key = match ($pp->workflow_status) {
                ProcurementPackage::WORKFLOW_PROVIDER_SELECTION => 'persiapan',
                ProcurementPackage::WORKFLOW_EXECUTION,
                ProcurementPackage::WORKFLOW_PAYMENT_PROCESS    => 'diproses',
                ProcurementPackage::WORKFLOW_COMPLETED          => 'selesai',
                default                                         => 'draft',
            };
            $stats[$key]['count']++;
            $stats[$key]['total'] += (float) ($pp->package->pagu ?? 0);
        });

        $status = $request->input('status');

        $procurementPackages = (clone $base)
            ->with(['package.program'])
            ->when($status, function ($q) use ($status) {
                match ($status) {
                    'draft'     => $q->where('workflow_status', ProcurementPackage::WORKFLOW_DRAFT),
                    'persiapan' => $q->where('workflow_status', ProcurementPackage::WORKFLOW_PROVIDER_SELECTION),
                    'diproses'  => $q->whereIn('workflow_status', [
                        ProcurementPackage::WORKFLOW_EXECUTION,
                        ProcurementPackage::WORKFLOW_PAYMENT_PROCESS,
                    ]),
                    'selesai'   => $q->where('workflow_status', ProcurementPackage::WORKFLOW_COMPLETED),
                    default     => null,
                };
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $programs = \App\Models\Program::orderBy('kode')->get();

        return view('kabid.penyedia.index', compact('procurementPackages', 'programs', 'stats', 'status'));
    }
}
