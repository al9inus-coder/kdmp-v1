<?php

namespace App\Http\Controllers;

use App\Http\Requests\PackageRequest;
use App\Models\Account;
use App\Models\FiscalYear;
use App\Models\Package;
use App\Models\Program;
use App\Models\SubActivity;
use App\Models\Activity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Gate;

class PackageController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Package::class);

        $search = $request->q;
        $status = $request->status;
        $programId = $request->program_id;
        $activityId = $request->activity_id;
        $subActivityId = $request->sub_activity_id;
        $fiscalYearId = $request->fiscal_year_id;

        $programs = Program::query()
            ->orderBy('kode')
            ->get();
        $fiscalYears = FiscalYear::query()
            ->orderBy('tahun', 'desc')
            ->get();
        $activities = Activity::query()
            ->orderBy('kode')
            ->get();
        $subActivities = SubActivity::query()
            ->orderBy('kode')
            ->get();

        $packages = Package::query()
            ->with([
                'program',
                'activity',
                'subActivity',
                'account',
                'fiscalYear',
            ])
            ->when($search, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('id_rup', 'like', "%{$search}%")
                        ->orWhere('nama_paket', 'like', "%{$search}%");
                });
            })
            ->when($status !== null && $status !== '', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($fiscalYearId !== null && $fiscalYearId !== '', function ($query) use ($fiscalYearId) {
                $query->where('fiscal_year_id', $fiscalYearId);
            })
            ->when($programId !== null && $programId !== '', function ($query) use ($programId) {
                $query->where('program_id', $programId);
            })
            ->when($activityId !== null && $activityId !== '', function ($query) use ($activityId) {
                $query->where('activity_id', $activityId);
            })
            ->when($subActivityId !== null && $subActivityId !== '', function ($query) use ($subActivityId) {
                $query->where('sub_activity_id', $subActivityId);
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('packages.index', compact(
            'packages',
            'programs',
            'activities',
            'subActivities',
            'fiscalYears',
            'search',
            'status',
            'programId',
            'activityId',
            'subActivityId',
            'fiscalYearId'
        ));
    }

    public function create(): View
    {
        Gate::authorize('create', Package::class);

        $package = new Package([
            'status' => 'draft',
        ]);
        $fiscalYears = FiscalYear::query()
            ->orderBy('tahun', 'desc')
            ->get();
        $subActivities = SubActivity::query()
            ->with(['activity.program'])
            ->orderBy('kode')
            ->get();
        $accounts = Account::query()
            ->orderBy('kode')
            ->get();

        return view('packages.create', compact(
            'package',
            'fiscalYears',
            'subActivities',
            'accounts'
        ));
    }

public function store(PackageRequest $request): RedirectResponse
{
    Gate::authorize('create', Package::class);

    $validated = $request->validated();

    $subActivity = null;

    if (!empty($validated['sub_activity_id'])) {
        $subActivity = SubActivity::query()
            ->with('activity')
            ->find($validated['sub_activity_id']);
    }

    $package = new Package([
        'import_batch_id' => null,
        'fiscal_year_id' => $validated['fiscal_year_id'],
        'program_id' => $subActivity?->activity?->program_id,
        'activity_id' => $subActivity?->activity_id,
        'sub_activity_id' => $validated['sub_activity_id'] ?? null,
        'account_id' => $validated['account_id'] ?? null,
        'id_rup' => $validated['id_rup'] ?? null,
        'nama_paket' => $validated['nama_paket'],
        'pagu' => $validated['pagu'],
        'jenis_pengadaan' => $validated['jenis_pengadaan'] ?? null,
        'metode_pengadaan' => $validated['metode_pengadaan'] ?? null,
        'pemilihan_mulai_bulan' => $validated['pemilihan_mulai_bulan'] ?? null,
        'pemilihan_selesai_bulan' => $validated['pemilihan_selesai_bulan'] ?? null,
        'kontrak_mulai_bulan' => $validated['kontrak_mulai_bulan'] ?? null,
        'kontrak_selesai_bulan' => $validated['kontrak_selesai_bulan'] ?? null,
    ]);

    $package->status = $package->isComplete()
        ? 'draft'
        : 'needs_review';

    $package->save();

    if ($request->input('source') === 'staf') {
        return redirect()
            ->route('staf.packages.create')
            ->with('success', 'Paket Pekerjaan berhasil ditambahkan.');
    }

    return redirect()
        ->route('packages.index')
        ->with('success', 'Paket Pekerjaan berhasil ditambahkan.');
}

    public function show(Package $package): View
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

        return view('packages.show', compact('package'));
    }

    public function byProgram(Request $request, Program $program): View
    {
        Gate::authorize('viewAny', Package::class);

        $search = $request->q;
        $status = $request->status;
        $fiscalYearId = $request->fiscal_year_id;
        $fiscalYears = FiscalYear::query()
            ->orderBy('tahun', 'desc')
            ->get();

        $packages = Package::query()
            ->with([
                'fiscalYear',
                'activity',
                'subActivity',
            ])
            ->where('program_id', $program->id)
            ->when($search, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('id_rup', 'like', "%{$search}%")
                        ->orWhere('nama_paket', 'like', "%{$search}%");
                });
            })
            ->when($status !== null && $status !== '', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($fiscalYearId !== null && $fiscalYearId !== '', function ($query) use ($fiscalYearId) {
                $query->where('fiscal_year_id', $fiscalYearId);
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('packages.program', compact(
            'program',
            'packages',
            'fiscalYears',
            'search',
            'status',
            'fiscalYearId'
        ));
    }

    public function edit(Package $package): View
    {
        Gate::authorize('update', $package);

        $fiscalYears = FiscalYear::query()
            ->orderBy('tahun', 'desc')
            ->get();

        $subActivities = SubActivity::query()
            ->with(['activity.program'])
            ->orderBy('kode')
            ->get();

        $accounts = Account::query()
            ->orderBy('kode')
            ->get();

        return view('packages.edit', compact(
            'package',
            'fiscalYears',
            'subActivities',
            'accounts'
        ));
    }

public function update(
    PackageRequest $request,
    Package $package
): RedirectResponse
{
    Gate::authorize('update', $package);

    $validated = $request->validated();

    $subActivity = null;

    if (!empty($validated['sub_activity_id'])) {
        $subActivity = SubActivity::query()
            ->with('activity')
            ->find($validated['sub_activity_id']);
    }

    $package->fill([
        'fiscal_year_id' => $validated['fiscal_year_id'],
        'program_id' => $subActivity?->activity?->program_id,
        'activity_id' => $subActivity?->activity_id,
        'sub_activity_id' => $validated['sub_activity_id'] ?? null,
        'account_id' => $validated['account_id'] ?? null,
        'id_rup' => $validated['id_rup'] ?? null,
        'nama_paket' => $validated['nama_paket'],
        'pagu' => $validated['pagu'],
        'jenis_pengadaan' => $validated['jenis_pengadaan'] ?? null,
        'metode_pengadaan' => $validated['metode_pengadaan'] ?? null,
        'pemilihan_mulai_bulan' => $validated['pemilihan_mulai_bulan'] ?? null,
        'pemilihan_selesai_bulan' => $validated['pemilihan_selesai_bulan'] ?? null,
        'kontrak_mulai_bulan' => $validated['kontrak_mulai_bulan'] ?? null,
        'kontrak_selesai_bulan' => $validated['kontrak_selesai_bulan'] ?? null,
    ]);

    $package->status = $package->isComplete()
        ? 'draft'
        : 'needs_review';

    $package->save();

    if ($request->input('source') === 'staf') {
        return redirect()
            ->route('staf.packages.show', $package)
            ->with('success', 'Paket berhasil diperbarui.');
    }

    return redirect()
        ->route('packages.show', $package)
        ->with('success', 'Paket berhasil diperbarui.');
}

    public function submit(Package $package): RedirectResponse
    {
        Gate::authorize('submit', $package);

        if ($package->status !== 'draft') {
            return back()->with(
                'error',
                'Hanya paket berstatus Draft yang dapat diajukan.'
            );
        }

        $package->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'submitted_by' => auth()->id(),
        ]);

        return back()->with(
            'success',
            'Paket berhasil diajukan.'
        );
    }

    public function approve(Package $package): RedirectResponse
    {
        Gate::authorize('approve', $package);

        if ($package->status !== 'submitted') {
            return back()->with(
                'error',
                'Hanya paket berstatus Submitted yang dapat disetujui.'
            );
        }

        $package->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        return back()->with(
            'success',
            'Paket berhasil disetujui.'
        );
    }

    public function returnToDraft(Package $package): RedirectResponse
    {
        Gate::authorize('returnToDraft', $package);

        if ($package->status !== 'submitted') {
            return back()->with(
                'error',
                'Hanya paket berstatus Submitted yang dapat dikembalikan ke Draft.'
            );
        }

        $package->update([
            'status' => 'draft',
            'approved_at' => null,
            'approved_by' => null,
        ]);

        return back()->with(
            'success',
            'Paket dikembalikan ke Draft.'
        );
    }
    public function programMenu(): JsonResponse
    {
        $programs = Program::query()
            ->orderBy('kode')
            ->get(['id', 'kode', 'nama']);

        return response()->json([
            'data' => $programs,
        ]);
    }

    public function procurement(Package $package)
    {
        return view('packages.procurement', compact('package'));
    }



    public function destroy(Request $request, Package $package): RedirectResponse
    {
        Gate::authorize('delete', $package);

        // Hapus data relasi terlebih dahulu untuk menghindari FK constraint
        $package->procurementPackage()->delete();

        $package->delete();

        if ($request->input('source') === 'staf') {
            return redirect()
                ->route('staf.packages.index')
                ->with('success', 'Paket Pekerjaan berhasil dihapus.');
        }

        return redirect()
            ->route('packages.index')
            ->with('success', 'Paket Pekerjaan berhasil dihapus.');
    }
}
