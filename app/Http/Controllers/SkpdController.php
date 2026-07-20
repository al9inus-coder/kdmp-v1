<?php

namespace App\Http\Controllers;

use App\Models\Skpd;
use Illuminate\Http\Request;

class SkpdController extends Controller
{
    public function index()
    {
        $skpds = Skpd::latest()->get();

        return view('skpds.index', compact('skpds'));
    }

    public function create()
    {
        return view('skpds.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode' => 'required|unique:skpds',
            'nama' => 'required',
            // other fields are nullable, so we can just grab all
        ]);

        Skpd::create($request->all());

        return redirect()
            ->route('admin.skpds.index')
            ->with('success', 'SKPD berhasil ditambahkan');
    }

    public function edit(Skpd $skpd)
    {
        return view('skpds.edit', compact('skpd'));
    }

    public function update(Request $request, Skpd $skpd)
    {
        $request->validate([
            'kode' => 'required|unique:skpds,kode,' . $skpd->id,
            'nama' => 'required',
        ]);

        $skpd->update($request->all());

        return redirect()
            ->route('admin.skpds.index')
            ->with('success', 'Data SKPD berhasil diperbarui');
    }

    public function destroy(Skpd $skpd)
    {
        $skpd->delete();

        return redirect()
            ->route('admin.skpds.index')
            ->with('success', 'Data SKPD berhasil dihapus');
    }
}
