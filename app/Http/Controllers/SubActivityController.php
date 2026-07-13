<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubActivityRequest;
use App\Models\Activity;
use App\Models\SubActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubActivityController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->q;
        $status = $request->status;
        $activityId = $request->activity_id;

        $activities = Activity::query()
            ->orderBy('kode')
            ->get();

        $subActivities = SubActivity::query()
            ->with('activity')
            ->when($search, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('kode', 'like', "%{$search}%")
                        ->orWhere('nama', 'like', "%{$search}%");
                });
            })
            ->when($status !== null && $status !== '', function ($query) use ($status) {
                $query->where('is_active', $status);
            })
            ->when($activityId !== null && $activityId !== '', function ($query) use ($activityId) {
                $query->where('activity_id', $activityId);
            })
            ->orderBy('kode')
            ->paginate(10)
            ->withQueryString();

        return view('sub_activities.index', compact(
            'subActivities',
            'activities',
            'search',
            'status',
            'activityId'
        ));
    }

    public function create(): View
    {
        $subActivity = new SubActivity([
            'is_active' => true,
        ]);
        $activities = Activity::query()
            ->orderBy('kode')
            ->get();

        return view('sub_activities.create', compact('subActivity', 'activities'));
    }

    public function store(SubActivityRequest $request): RedirectResponse
    {
        SubActivity::create($request->validated());

        return redirect()
            ->route('admin.sub-activities.index')
            ->with('success', 'Sub Kegiatan berhasil ditambahkan.');
    }

    public function edit(SubActivity $subActivity): View
    {
        $activities = Activity::query()
            ->orderBy('kode')
            ->get();

        return view('sub_activities.edit', compact('subActivity', 'activities'));
    }

    public function update(SubActivityRequest $request, SubActivity $subActivity): RedirectResponse
    {
        $subActivity->update($request->validated());

        return redirect()
            ->route('admin.sub-activities.index')
            ->with('success', 'Sub Kegiatan berhasil diperbarui.');
    }
}
