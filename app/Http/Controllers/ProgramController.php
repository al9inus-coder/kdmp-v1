<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProgramRequest;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProgramController extends Controller
{
    public function index(\Illuminate\Http\Request $request): View
    {
        $search = $request->q;
        $status = $request->status;    
        //$search = request('q');
        $programs = Program::query()
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

        return view('programs.index',compact('programs', 'search', 'status'));
    }

    public function create(): View
    {
        $program = new Program([
            'is_active' => true,
        ]);

        return view('programs.create', compact('program'));
    }

    public function store(ProgramRequest $request): RedirectResponse
    {
        Program::create($request->validated());

        return redirect()
            ->route('admin.programs.index')
            ->with('success', 'Program berhasil ditambahkan.');
    }

    public function edit(Program $program): View
    {
        return view('programs.edit', compact('program'));
    }

    public function update(ProgramRequest $request, Program $program): RedirectResponse
    {
        $program->update($request->validated());

        return redirect()
            ->route('admin.programs.index')
            ->with('success', 'Program berhasil diperbarui.');
    }
}
