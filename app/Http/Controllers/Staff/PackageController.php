<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ImportBatch;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $search        = $request->input('search');
        $statusFilter  = $request->input('status');
        $programId     = $request->input('program_id');
        $activityId    = $request->input('activity_id');
        $subActivityId = $request->input('sub_activity_id');

        $filtered = \App\Models\Package::query()
            ->when($search, fn($q) => $q->where(fn($sub) =>
                $sub->where('nama_paket', 'like', "%{$search}%")
                    ->orWhere('id_rup', 'like', "%{$search}%")
            ))
            ->when($programId,     fn($q) => $q->where('program_id', $programId))
            ->when($activityId,    fn($q) => $q->where('activity_id', $activityId))
            ->when($subActivityId, fn($q) => $q->where('sub_activity_id', $subActivityId));

        // Jumlah paket per status (mengikuti filter lain, kecuali status itu sendiri)
        $statusCounts = (clone $filtered)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $packages = (clone $filtered)
            ->with(['program', 'activity', 'subActivity'])
            ->when($statusFilter, fn($q) => $q->where('status', $statusFilter))
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $programs     = \App\Models\Program::orderBy('kode')->get();
        $activities   = \App\Models\Activity::when($programId, fn($q) => $q->where('program_id', $programId))->orderBy('kode')->get();
        $subActivities = \App\Models\SubActivity::when($activityId, fn($q) => $q->where('activity_id', $activityId))->orderBy('kode')->get();

        return view('staf.packages.index', compact(
            'packages', 'programs', 'activities', 'subActivities', 'statusCounts',
            'search', 'statusFilter', 'programId', 'activityId', 'subActivityId'
        ));
    }

    public function create()
    {
        $fiscalYears = \App\Models\FiscalYear::orderBy('tahun', 'desc')->get();
        $subActivities = \App\Models\SubActivity::orderBy('kode', 'asc')->get();
        $accounts = \App\Models\Account::orderBy('kode', 'asc')->get();
        
        return view('staf.packages.create', compact('fiscalYears', 'subActivities', 'accounts'));
    }

    public function show(\App\Models\Package $package)
    {
        Gate::authorize('view', $package);

        $package->load([
            'fiscalYear',
            'program',
            'activity',
            'subActivity',
            'account',
            'importBatch',
            'procurementPackage',
        ]);

        return view('staf.packages.show', compact('package'));
    }

    public function edit(\App\Models\Package $package)
    {
        Gate::authorize('update', $package);

        $fiscalYears = \App\Models\FiscalYear::orderBy('tahun', 'desc')->get();
        $subActivities = \App\Models\SubActivity::orderBy('kode', 'asc')->get();
        $accounts = \App\Models\Account::orderBy('kode', 'asc')->get();
        
        return view('staf.packages.edit', compact('package', 'fiscalYears', 'subActivities', 'accounts'));
    }
}
