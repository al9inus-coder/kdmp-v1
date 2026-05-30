<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActivityRequest;
use App\Models\Activity;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->q;
        $status = $request->status;

        $activities = Activity::query()
            ->with('program')
            ->when($search, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('kode', 'like', "%{$search}%")
                        ->orWhere('nama', 'like', "%{$search}%");
                });
            })
            ->when($status !== null && $status !== '', function ($query) use ($status) {
                $query->where('is_active', $status);
            })
            ->orderBy('kode')
            ->paginate(10)
            ->withQueryString();

        return view('activities.index', compact('activities', 'search', 'status'));
    }

    public function create(): View
    {
        $activity = new Activity([
            'is_active' => true,
        ]);
        $programs = Program::query()
            ->orderBy('kode')
            ->get();

        return view('activities.create', compact('activity', 'programs'));
    }

    public function store(ActivityRequest $request): RedirectResponse
    {
        Activity::create($request->validated());

        return redirect()
            ->route('activities.index')
            ->with('success', 'Kegiatan berhasil ditambahkan.');
    }

    public function edit(Activity $activity): View
    {
        $programs = Program::query()
            ->orderBy('kode')
            ->get();

        return view('activities.edit', compact('activity', 'programs'));
    }

    public function update(ActivityRequest $request, Activity $activity): RedirectResponse
    {
        $activity->update($request->validated());

        return redirect()
            ->route('activities.index')
            ->with('success', 'Kegiatan berhasil diperbarui.');
    }
}
