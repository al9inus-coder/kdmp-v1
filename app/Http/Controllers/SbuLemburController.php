<?php

namespace App\Http\Controllers;

use App\Models\SbuLembur;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SbuLemburController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $uangLemburs = SbuLembur::where('jenis', 'Uang Lembur')->orderBy('besaran', 'desc')->get();
        $uangMakanLemburs = SbuLembur::where('jenis', 'Uang Makan Lembur')->orderBy('besaran', 'desc')->get();

        return view('sbu-lemburs.index', compact('uangLemburs', 'uangMakanLemburs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'jenis' => 'required|string',
            'golongan' => 'required|string',
            'satuan' => 'required|string',
            'besaran' => 'required|numeric|min:0',
        ]);

        SbuLembur::create($validated);

        return redirect()->route('admin.sbu-lemburs.index')->with('success', 'Standar Biaya Lembur berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SbuLembur $sbu_lembur): RedirectResponse
    {
        $validated = $request->validate([
            'jenis' => 'required|string',
            'golongan' => 'required|string',
            'satuan' => 'required|string',
            'besaran' => 'required|numeric|min:0',
        ]);

        $sbu_lembur->update($validated);

        return redirect()->route('admin.sbu-lemburs.index')->with('success', 'Standar Biaya Lembur berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SbuLembur $sbu_lembur): RedirectResponse
    {
        $sbu_lembur->delete();

        return redirect()->route('admin.sbu-lemburs.index')->with('success', 'Standar Biaya Lembur berhasil dihapus.');
    }
}
