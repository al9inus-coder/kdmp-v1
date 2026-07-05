<?php

namespace App\Http\Controllers;

use App\Models\SbuUangHarian;
use Illuminate\Http\Request;

class SbuUangHarianController extends Controller
{
    public function index()
    {
        $rates = SbuUangHarian::orderBy('provinsi')->paginate(20);
        return view('sbu-uang-harians.index', compact('rates'));
    }

    public function create()
    {
        return view('sbu-uang-harians.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'provinsi' => 'required|string|max:255',
            'satuan' => 'required|string|max:50',
            'luar_kota' => 'required|numeric|min:0',
            'diklat' => 'required|numeric|min:0',
        ]);

        SbuUangHarian::create($validated);

        return redirect()->route('sbu-uang-harians.index')->with('success', 'Standar Uang Harian berhasil ditambahkan.');
    }

    public function edit(SbuUangHarian $sbuUangHarian)
    {
        return view('sbu-uang-harians.edit', compact('sbuUangHarian'));
    }

    public function update(Request $request, SbuUangHarian $sbuUangHarian)
    {
        $validated = $request->validate([
            'provinsi' => 'required|string|max:255',
            'satuan' => 'required|string|max:50',
            'luar_kota' => 'required|numeric|min:0',
            'diklat' => 'required|numeric|min:0',
        ]);

        $sbuUangHarian->update($validated);

        return redirect()->route('sbu-uang-harians.index')->with('success', 'Standar Uang Harian berhasil diperbarui.');
    }

    public function destroy(SbuUangHarian $sbuUangHarian)
    {
        $sbuUangHarian->delete();
        return back()->with('success', 'Standar Uang Harian berhasil dihapus.');
    }
}
