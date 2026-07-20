<?php

namespace App\Http\Controllers;

use App\Models\ProcurementPackage;
use App\Models\ProcurementExternalRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProcurementExternalRecordController extends Controller
{
    public function store(Request $request, ProcurementPackage $procurementPackage)
    {
        $this->authorizePackage($procurementPackage);

        $data = $request->validate([
            'surat_pesanan_no' => 'nullable|string|max:255',
            'surat_pesanan_tgl' => 'nullable|date',
            'surat_tagihan_no' => 'nullable|string|max:255',
            'surat_tagihan_tgl' => 'nullable|date',
            'bast_no' => 'nullable|string|max:255',
            'bast_tgl' => 'nullable|date',
            'bap_no' => 'nullable|string|max:255',
            'bap_tgl' => 'nullable|date',
            'kwitansi_no' => 'nullable|string|max:255',
            'kwitansi_tgl' => 'nullable|date',
            'nilai_kontrak' => 'nullable|numeric|min:0',
        ]);

        $procurementPackage->externalRecords()->create($data);

        return back()->with('success', 'Transaksi berhasil ditambahkan.');
    }

    public function destroy(ProcurementPackage $procurementPackage, ProcurementExternalRecord $externalRecord)
    {
        $this->authorizePackage($procurementPackage);
        $this->authorizeRecord($procurementPackage, $externalRecord);

        $externalRecord->delete();

        return back()->with('success', 'Transaksi berhasil dihapus.');
    }

    public function print(ProcurementPackage $procurementPackage, ProcurementExternalRecord $externalRecord)
    {
        $this->authorizePackage($procurementPackage);
        $this->authorizeRecord($procurementPackage, $externalRecord);

        $procurementPackage->load([
            'package.subActivity.activity.program',
            'package.account'
        ]);

        $skpd = \App\Models\Skpd::first();

        return view('kwitansi.print', compact('procurementPackage', 'externalRecord', 'skpd'));
    }

    private function authorizePackage(ProcurementPackage $procurementPackage): void
    {
        $procurementPackage->loadMissing('package');
        abort_unless($procurementPackage->package, 404);
        Gate::authorize('view', $procurementPackage->package);
    }

    private function authorizeRecord(ProcurementPackage $procurementPackage, ProcurementExternalRecord $externalRecord): void
    {
        abort_unless((int) $externalRecord->procurement_package_id === (int) $procurementPackage->getKey(), 404);
    }
}
