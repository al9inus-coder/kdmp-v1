<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Package;
use App\Models\ProcurementPackage;
use App\Models\ProcurementProcess;

class ProcurementProcessController extends Controller
{
    public function completePreparation(Package $package)
    {
        $procurementPackage = $package->procurementPackage;
        abort_if(!$procurementPackage, 404);

        // Validasi ketersediaan Spesifikasi Teknis dan Referensi Harga (KAK dan HPS diabaikan untuk saat ini)
        if (!$procurementPackage->technicalSpecification) {
            return back()->with('error', 'Spesifikasi Teknis belum diisi.');
        }

        if (!$procurementPackage->priceReferences()->exists()) {
            return back()->with('error', 'Referensi Harga belum diisi.');
        }

        $procurementPackage->update([
            'workflow_status' => ProcurementPackage::WORKFLOW_PROVIDER_SELECTION
        ]);

        return redirect()->route((auth()->user()->hasRole('Kabid') ? 'kabid.' : 'admin.') . 'procurement-packages.show', $procurementPackage->package)
            ->with('success', 'Persiapan pengadaan telah selesai. Paket pengadaan kini terkunci dan siap untuk tahap selanjutnya.');
    }

    public function show(Package $package)
    {
        $procurementPackage = $package->procurementPackage;
        abort_if(!$procurementPackage, 404);

        // Pastikan proses sudah melewati tahap draft
        if ($procurementPackage->workflow_status === ProcurementPackage::WORKFLOW_DRAFT) {
            return redirect()->route((auth()->user()->hasRole('Kabid') ? 'kabid.' : 'admin.') . 'procurement-packages.show', $package)
                ->with('error', 'Selesaikan tahap Persiapan Pengadaan terlebih dahulu.');
        }

        $process = $procurementPackage->procurementProcess()->firstOrCreate([
            'procurement_package_id' => $procurementPackage->id
        ], [
            'nama_penyedia' => $procurementPackage->procurementRequest?->nama_penyedia
        ]);

        return view('procurement-processes.show', compact('procurementPackage', 'process'));
    }

    public function update(Request $request, Package $package)
    {
        $procurementPackage = $package->procurementPackage;
        abort_if(!$procurementPackage, 404);

        $validated = $request->validate([
            'nomor_surat_pesanan' => 'required|string|max:255',
            'tanggal_surat_pesanan' => 'required|date',
            'nama_penyedia' => 'required|string|max:255',
            'alamat_penyedia' => 'required|string',
            'npwp_penyedia' => 'required|string|max:255',
            'tanggal_barang_diterima' => 'required|date|after_or_equal:tanggal_surat_pesanan',
            'catatan' => 'nullable|string',
            'nilai_kontrak' => 'required|string',
            'nomor_rekening' => 'required|string|max:255',
            'nama_bank' => 'required|string|max:255',
            'nama_pic' => 'required|string|max:255',
            'jabatan_pic' => 'required|string|max:255',
        ]);

        $validated['nilai_kontrak'] = (int) str_replace('.', '', $validated['nilai_kontrak']);

        $process = $procurementPackage->procurementProcess()->firstOrCreate([
            'procurement_package_id' => $procurementPackage->id
        ], [
            'nama_penyedia' => $procurementPackage->procurementRequest?->nama_penyedia
        ]);

        $process->update($validated);

        return redirect()->back()->with('success', 'Data Surat Pesanan berhasil disimpan.');
    }

    public function previewDocument(Package $package)
    {
        $procurementPackage = $package->procurementPackage;
        abort_if(!$procurementPackage, 404);
        
        $process = $procurementPackage->procurementProcess;
        abort_if(!$process, 404);

        return view('procurement-processes.preview-document', compact('procurementPackage', 'process'));
    }

    public function printDocument(Package $package)
    {
        $procurementPackage = $package->procurementPackage;
        abort_if(!$procurementPackage, 404);

        $process = $procurementPackage->procurementProcess;
        abort_if(!$process, 404);

        return view('procurement-processes.print-document', compact('procurementPackage', 'process'));
    }

    public function startExecution(Package $package)
    {
        $procurementPackage = $package->procurementPackage;
        abort_if(!$procurementPackage, 404);

        $procurementPackage->update([
            'workflow_status' => ProcurementPackage::WORKFLOW_EXECUTION
        ]);

        $prefix = auth()->user()->hasRole('Admin') ? 'admin' : 'kabid';
        return redirect()->route($prefix . '.procurement-packages.execution.show', $package)
            ->with('success', 'Paket pengadaan telah masuk ke tahap Pelaksanaan Kontrak.');
    }

    public function execution(Package $package)
    {
        $procurementPackage = $package->procurementPackage;
        abort_if(!$procurementPackage, 404);

        $process = $procurementPackage->procurementProcess;
        abort_if(!$process, 404);

        $payment = $procurementPackage->payment ?? new \App\Models\ProcurementPayment();
        
        if (!$procurementPackage->payment) {
            // Auto-fill PPTK dari Master SKPD
            $skpd = \App\Models\Skpd::first();
            if ($skpd) {
                $payment->nama_pptk = $skpd->nama_pptk;
                $payment->nip_pptk = $skpd->nip_pptk;
                $payment->pangkat_golongan_pptk = $skpd->pangkat_pptk;
            }
        }

        return view('procurement-processes.execution', compact('procurementPackage', 'process', 'payment'));
    }
}
