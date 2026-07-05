<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\ProcurementPackage;

class ProcurementPackageController extends Controller
{
    public function show(Package $package)
    {
        $procurementPackage = $package->procurementPackage;

        abort_if(!$procurementPackage, 404);

        $procurementPackage->load([
            'package.fiscalYear',
            'package.program',
            'package.activity',
            'package.subActivity',
            'package.account',
            'creator',
            'technicalSpecification.items',
            'procurementRequest',
            'priceReferences',
        ]);

        return view('admin.procurement-packages.show', compact('procurementPackage'));
    }

    public function payment(Package $package)
    {
        $procurementPackage = $package->procurementPackage;

        abort_if(!$procurementPackage, 404);

        // Halaman ini relevan setelah pekerjaan selesai (ada data pembayaran)
        if (!in_array($procurementPackage->workflow_status, [
            ProcurementPackage::WORKFLOW_PAYMENT_PROCESS,
            ProcurementPackage::WORKFLOW_COMPLETED,
        ])) {
            return redirect()
                ->route('admin.procurement-packages.show', $package)
                ->with('warning', 'Paket ini belum mencapai tahap Pembayaran.');
        }

        $process = $procurementPackage->procurementProcess;
        $payment = $procurementPackage->payment;

        abort_if(!$process || !$payment, 404);

        $procurementPackage->load(['package.fiscalYear', 'package.program']);

        return view('admin.procurement-packages.payment', compact('procurementPackage', 'process', 'payment'));
    }

    public function unlock(Package $package)
    {
        $procurementPackage = $package->procurementPackage;

        abort_if(!$procurementPackage, 404);

        if ($procurementPackage->workflow_status === ProcurementPackage::WORKFLOW_DRAFT) {
            return redirect()
                ->route('admin.procurement-packages.show', $package)
                ->with('warning', 'Persiapan paket ini tidak sedang terkunci.');
        }

        $procurementPackage->update([
            'workflow_status' => ProcurementPackage::WORKFLOW_DRAFT,
        ]);

        return redirect()
            ->route('admin.procurement-packages.show', $package)
            ->with('success', 'Kunci persiapan dibuka. Kabid dapat mengubah data persiapan kembali.');
    }

    public function unlockSelection(Package $package)
    {
        $procurementPackage = $package->procurementPackage;

        abort_if(!$procurementPackage, 404);

        $terkunci = in_array($procurementPackage->workflow_status, [
            ProcurementPackage::WORKFLOW_EXECUTION,
            ProcurementPackage::WORKFLOW_PAYMENT_PROCESS,
            ProcurementPackage::WORKFLOW_COMPLETED,
        ]);

        if (!$terkunci) {
            return redirect()
                ->route('admin.procurement-packages.show', $package)
                ->with('warning', 'Tahap pemilihan penyedia paket ini tidak sedang terkunci.');
        }

        $procurementPackage->update([
            'workflow_status' => ProcurementPackage::WORKFLOW_PROVIDER_SELECTION,
        ]);

        return redirect()
            ->route('admin.procurement-packages.show', $package)
            ->with('success', 'Kunci pemilihan penyedia dibuka. Kabid dapat mengubah data pemilihan kembali.');
    }

    public function unlockPayment(Package $package)
    {
        $procurementPackage = $package->procurementPackage;

        abort_if(!$procurementPackage, 404);

        if ($procurementPackage->workflow_status !== ProcurementPackage::WORKFLOW_COMPLETED) {
            return redirect()
                ->route('admin.procurement-packages.show', $package)
                ->with('warning', 'Paket ini belum berstatus selesai.');
        }

        $procurementPackage->update([
            'status' => 'draft',
            'workflow_status' => ProcurementPackage::WORKFLOW_PAYMENT_PROCESS,
        ]);

        return redirect()
            ->route('admin.procurement-packages.show', $package)
            ->with('success', 'Status selesai dibuka. Paket kembali ke tahap Pembayaran.');
    }

    public function unlockExecution(Package $package)
    {
        $procurementPackage = $package->procurementPackage;

        abort_if(!$procurementPackage, 404);

        $terkunci = $procurementPackage->workflow_status === ProcurementPackage::WORKFLOW_PAYMENT_PROCESS;

        if (!$terkunci) {
            return redirect()
                ->route('admin.procurement-packages.show', $package)
                ->with('warning', 'Tahap pelaksanaan kontrak paket ini tidak sedang terkunci.');
        }

        $procurementPackage->update([
            'workflow_status' => ProcurementPackage::WORKFLOW_EXECUTION,
        ]);

        return redirect()
            ->route('admin.procurement-packages.show', $package)
            ->with('success', 'Kunci pelaksanaan dibuka. Paket kembali ke tahap Pelaksanaan Kontrak.');
    }
}
