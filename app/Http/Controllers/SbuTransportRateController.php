<?php

namespace App\Http\Controllers;

use App\Models\SbuTransportRate;
use Illuminate\Http\Request;

class SbuTransportRateController extends Controller
{
    public function index()
    {
        $rates = SbuTransportRate::orderBy('tempat_kedudukan')->orderBy('tempat_tujuan')->paginate(20);
        return view('sbu-transport-rates.index', compact('rates'));
    }

    public function create()
    {
        return view('sbu-transport-rates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tempat_kedudukan' => 'required|string|max:255',
            'tempat_tujuan' => 'required|string|max:255',
            'satuan' => 'required|string|max:50',
            'biaya_mobil' => 'required|numeric|min:0',
            'biaya_motor' => 'required|numeric|min:0',
        ]);

        SbuTransportRate::create($validated);

        return redirect()->route('sbu-transport-rates.index')->with('success', 'Standar Biaya Transportasi berhasil ditambahkan.');
    }

    public function edit(SbuTransportRate $sbuTransportRate)
    {
        return view('sbu-transport-rates.edit', compact('sbuTransportRate'));
    }

    public function update(Request $request, SbuTransportRate $sbuTransportRate)
    {
        $validated = $request->validate([
            'tempat_kedudukan' => 'required|string|max:255',
            'tempat_tujuan' => 'required|string|max:255',
            'satuan' => 'required|string|max:50',
            'biaya_mobil' => 'required|numeric|min:0',
            'biaya_motor' => 'required|numeric|min:0',
        ]);

        $sbuTransportRate->update($validated);

        return redirect()->route('sbu-transport-rates.index')->with('success', 'Standar Biaya Transportasi berhasil diperbarui.');
    }

    public function destroy(SbuTransportRate $sbuTransportRate)
    {
        $sbuTransportRate->delete();
        return redirect()->route('sbu-transport-rates.index')->with('success', 'Standar Biaya Transportasi berhasil dihapus.');
    }
}
