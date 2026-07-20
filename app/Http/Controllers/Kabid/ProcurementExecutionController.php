<?php

namespace App\Http\Controllers\Kabid;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\ProcurementPackage;
use App\Models\Skpd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProcurementExecutionController extends Controller
{
    public function show(Package $package)
    {
        Gate::authorize('view', $package);

        $procurementPackage = $package->procurementPackage;

        abort_if(!$procurementPackage, 404);

        // Tahap ini hanya bisa dibuka setelah pelaksanaan dimulai
        if (!in_array($procurementPackage->workflow_status, [
            ProcurementPackage::WORKFLOW_EXECUTION,
            ProcurementPackage::WORKFLOW_PAYMENT_PROCESS,
            ProcurementPackage::WORKFLOW_COMPLETED,
        ])) {
            return redirect()
                ->route('kabid.procurement-packages.procurement-process.show', $package)
                ->with('error', 'Mulai tahap Pelaksanaan Kontrak terlebih dahulu.')
                ->with('panel', 4);
        }

        $process = $procurementPackage->procurementProcess;

        abort_if(!$process, 404);

        $procurementPackage->load([
            'package.fiscalYear',
            'package.program',
            'package.activity',
            'package.subActivity',
            'addendums',
        ]);

        $payment = $procurementPackage->payment;

        // Prefill PPTK dari master SKPD (snapshot, tidak disimpan)
        $pptkPrefill = [
            'nama_pptk' => $payment->nama_pptk ?? null,
            'nip_pptk' => $payment->nip_pptk ?? null,
            'pangkat_golongan_pptk' => $payment->pangkat_golongan_pptk ?? null,
        ];
        if (!$payment && ($skpd = Skpd::first())) {
            $pptkPrefill = [
                'nama_pptk' => $skpd->nama_pptk,
                'nip_pptk' => $skpd->nip_pptk,
                'pangkat_golongan_pptk' => $skpd->pangkat_pptk,
            ];
        }

        return view('kabid.procurement-executions.show', compact('procurementPackage', 'process', 'payment', 'pptkPrefill'));
    }

    /**
     * Aksi pelaksanaan hanya boleh saat paket masih di tahap Pelaksanaan.
     */
    private function assertExecutionEditable(?ProcurementPackage $procurementPackage): void
    {
        abort_if(!$procurementPackage, 404);

        abort_if(
            $procurementPackage->workflow_status !== ProcurementPackage::WORKFLOW_EXECUTION,
            403,
            'Tahap pelaksanaan kontrak sudah diselesaikan dan terkunci.'
        );
    }

    public function storeAddendum(Request $request, Package $package)
    {
        Gate::authorize('view', $package);

        $procurementPackage = $package->procurementPackage;

        $this->assertExecutionEditable($procurementPackage);

        $data = $request->validate([
            'nomor' => 'required|string|max:255',
            'tanggal_akhir_baru' => 'required|date',
            'alasan' => 'required|string',
        ]);

        $procurementPackage->addendums()->create($data);

        // Geser batas akhir kontrak
        $procurementPackage->procurementProcess?->update([
            'tanggal_barang_diterima' => $data['tanggal_akhir_baru'],
        ]);

        return redirect()
            ->route('kabid.procurement-packages.execution.show', $package)
            ->with('success', 'Adendum tersimpan — batas akhir kontrak diperbarui.');
    }

    public function finishWork(Request $request, Package $package)
    {
        Gate::authorize('view', $package);

        $procurementPackage = $package->procurementPackage;

        $this->assertExecutionEditable($procurementPackage);

        $data = $request->validate([
            'nomor_bast' => 'required|string|max:255',
            'tanggal_bast' => 'required|date',
            'nomor_invoice' => 'required|string|max:255',
            'tanggal_invoice' => 'required|date',
            'nomor_bap' => 'required|string|max:255',
            'tanggal_bap' => 'required|date',
            'nomor_kwitansi' => 'required|string|max:255',
            'tanggal_kwitansi' => 'required|date',
            'tanggal_ringkasan_kontrak' => 'required|date',
            'is_non_pkp' => 'nullable|boolean',
            'tanggal_non_pkp' => 'required_if:is_non_pkp,1|date|nullable',
            'nama_pptk' => 'required|string|max:255',
            'nip_pptk' => 'required|string|max:255',
            'pangkat_golongan_pptk' => 'required|string|max:255',
        ]);

        $data['is_non_pkp'] = $request->boolean('is_non_pkp');

        $procurementPackage->payment()->updateOrCreate(
            ['procurement_package_id' => $procurementPackage->id],
            $data
        );

        $procurementPackage->update([
            'workflow_status' => ProcurementPackage::WORKFLOW_PAYMENT_PROCESS,
        ]);

        return redirect()
            ->route('kabid.procurement-packages.execution.show', $package)
            ->with('success', 'Pekerjaan selesai dicatat. Paket masuk tahap Pembayaran.');
    }
}
