<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\ProcurementPackage;
use App\Models\ProcurementAddendum;
use App\Models\ProcurementPayment;
use Illuminate\Http\Request;

class ProcurementPaymentController extends Controller
{
    public function storeAddendum(Request $request, Package $package)
    {
        $procurementPackage = $package->procurementPackage;
        
        $request->validate([
            'nomor' => 'required|string',
            'tanggal_akhir_baru' => 'required|date',
            'alasan' => 'required|string',
        ]);

        $procurementPackage->addendums()->create([
            'nomor' => $request->nomor,
            'tanggal_akhir_baru' => $request->tanggal_akhir_baru,
            'alasan' => $request->alasan,
        ]);

        // Update tanggal akhir di procurement_processes
        if ($procurementPackage->procurementProcess) {
            $procurementPackage->procurementProcess->update([
                'tanggal_barang_diterima' => $request->tanggal_akhir_baru,
            ]);
        }

        return redirect()->back()->with('success', 'Adendum Kontrak berhasil disimpan, batas waktu pelaksanaan telah diperbarui.');
    }

    public function storePayment(Request $request, Package $package)
    {
        $procurementPackage = $package->procurementPackage;
        
        $request->validate([
            'nomor_bast' => 'required|string',
            'tanggal_bast' => 'required|date',
            'nomor_invoice' => 'required|string',
            'tanggal_invoice' => 'required|date',
            'nomor_bap' => 'required|string',
            'tanggal_bap' => 'required|date',
            'nomor_kwitansi' => 'required|string',
            'tanggal_kwitansi' => 'required|date',
            'tanggal_ringkasan_kontrak' => 'required|date',
            'tanggal_non_pkp' => 'required_if:is_non_pkp,1|date|nullable',
            'nama_pptk' => 'required|string',
            'nip_pptk' => 'required|string',
            'pangkat_golongan_pptk' => 'required|string',
        ]);

        $procurementPackage->payment()->updateOrCreate(
            ['procurement_package_id' => $procurementPackage->id],
            [
                'nomor_bast' => $request->nomor_bast,
                'tanggal_bast' => $request->tanggal_bast,
                'nomor_invoice' => $request->nomor_invoice,
                'tanggal_invoice' => $request->tanggal_invoice,
                'nomor_bap' => $request->nomor_bap,
                'tanggal_bap' => $request->tanggal_bap,
                'nomor_kwitansi' => $request->nomor_kwitansi,
                'tanggal_kwitansi' => $request->tanggal_kwitansi,
                'is_non_pkp' => $request->boolean('is_non_pkp'),
                'tanggal_non_pkp' => $request->tanggal_non_pkp,
                'tanggal_ringkasan_kontrak' => $request->tanggal_ringkasan_kontrak,
                'nama_pptk' => $request->nama_pptk,
                'nip_pptk' => $request->nip_pptk,
                'pangkat_golongan_pptk' => $request->pangkat_golongan_pptk,
            ]
        );

        // Update workflow status
        $procurementPackage->update([
            'workflow_status' => ProcurementPackage::WORKFLOW_PAYMENT_PROCESS
        ]);

        // Nama route halaman pembayaran berbeda antar role:
        // admin.procurement-packages.payment vs kabid.procurement-packages.payment.show
        $tujuan = auth()->user()->hasAnyRole(['Admin', 'Super Admin'])
            ? 'admin.procurement-packages.payment'
            : 'kabid.procurement-packages.payment.show';

        return redirect()->route($tujuan, $package)->with('success', 'Pekerjaan dinyatakan Selesai. Selamat datang di tahap Pembayaran!');
    }

    public function show(Package $package)
    {
        $procurementPackage = $package->procurementPackage;
        $process = $procurementPackage->procurementProcess;
        $payment = $procurementPackage->payment;

        return view('procurement-payments.show', compact('procurementPackage', 'process', 'payment'));
    }

    public function previewDocument(Package $package)
    {
        $procurementPackage = $package->procurementPackage;
        $process = $procurementPackage->procurementProcess;
        $payment = $procurementPackage->payment;

        return view('procurement-payments.preview-document', compact('procurementPackage', 'process', 'payment'));
    }

    public function printDocument(Package $package, \Illuminate\Http\Request $request)
    {
        $procurementPackage = $package->procurementPackage;
        $process = $procurementPackage->procurementProcess;
        $payment = $procurementPackage->payment;
        $type = $request->get('type', 'all');

        return view('procurement-payments.print-document', compact('procurementPackage', 'process', 'payment', 'type'));
    }

    public function complete(Package $package)
    {
        $procurementPackage = $package->procurementPackage;
        
        $procurementPackage->update([
            'workflow_status' => ProcurementPackage::WORKFLOW_COMPLETED
        ]);

        return redirect()->route((auth()->user()->hasRole('Kabid') ? 'kabid.' : 'admin.') . 'procurement-packages.index')->with('success', 'Seluruh Proses Pengadaan telah selesai!');
    }
}
