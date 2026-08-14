<?php

namespace App\Http\Controllers;

use App\Models\TarifPajak;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PajakController extends Controller
{
    public function index(): View
    {
        // Dikelompokkan per jenis supaya halaman terbaca sebagai daftar acuan,
        // bukan tabel panjang yang mencampur PPh 21 lembur dengan PPN.
        $tarif = TarifPajak::orderBy('jenis')->orderBy('persen')->get()->groupBy('jenis');

        return view('pajak.index', compact('tarif'));
    }

    public function store(Request $request): RedirectResponse
    {
        TarifPajak::create($this->validasi($request));

        return redirect()->route('admin.pajak.index')->with('success', 'Tarif pajak berhasil ditambahkan.');
    }

    public function update(Request $request, TarifPajak $pajak): RedirectResponse
    {
        $pajak->update($this->validasi($request));

        return redirect()->route('admin.pajak.index')->with('success', 'Tarif pajak berhasil diperbarui.');
    }

    public function destroy(TarifPajak $pajak): RedirectResponse
    {
        $pajak->delete();

        return redirect()->route('admin.pajak.index')->with('success', 'Tarif pajak berhasil dihapus.');
    }

    private function validasi(Request $request): array
    {
        $data = $request->validate([
            'jenis' => 'required|string|in:' . implode(',', array_keys(TarifPajak::jenisOptions())),
            'kunci' => 'nullable|string|max:255',
            // Dibatasi 0–100 supaya salah ketik seperti 500 tidak lolos dan
            // memotong jauh melebihi nominalnya.
            'persen' => 'required|numeric|min:0|max:100',
            'dasar' => 'required|string|in:' . TarifPajak::DASAR_DPP . ',' . TarifPajak::DASAR_BRUTO,
            'keterangan' => 'nullable|string|max:255',
            'aktif' => 'nullable|boolean',
        ], [
            'persen.max' => 'Tarif pajak tidak boleh lebih dari 100%.',
        ]);

        $data['aktif'] = $request->boolean('aktif', true);

        return $data;
    }
}
