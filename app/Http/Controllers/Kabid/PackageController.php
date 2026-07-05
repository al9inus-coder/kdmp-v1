<?php

namespace App\Http\Controllers\Kabid;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Package::class);

        $search        = $request->input('search');
        $statusFilter  = $request->input('status');
        $programId     = $request->input('program_id');
        $activityId    = $request->input('activity_id');
        $subActivityId = $request->input('sub_activity_id');

        $filtered = Package::query()
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
            ->with(['program', 'activity', 'subActivity', 'submitter'])
            ->when($statusFilter, fn($q) => $q->where('status', $statusFilter))
            ->orderByRaw("CASE WHEN status = 'submitted' THEN 0 ELSE 1 END")
            ->orderByDesc('submitted_at')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $programs      = \App\Models\Program::orderBy('kode')->get();
        $activities    = \App\Models\Activity::when($programId, fn($q) => $q->where('program_id', $programId))->orderBy('kode')->get();
        $subActivities = \App\Models\SubActivity::when($activityId, fn($q) => $q->where('activity_id', $activityId))->orderBy('kode')->get();

        return view('kabid.packages.index', compact(
            'packages', 'programs', 'activities', 'subActivities', 'statusCounts',
            'search', 'statusFilter', 'programId', 'activityId', 'subActivityId'
        ));
    }

    public function show(Package $package)
    {
        Gate::authorize('view', $package);

        $package->load([
            'fiscalYear',
            'program',
            'activity',
            'subActivity',
            'account',
            'submitter',
            'approver',
            'procurementPackage',
        ]);

        return view('kabid.packages.show', compact('package'));
    }
}
