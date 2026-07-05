<?php

namespace App\Http\Controllers\Kabid;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\ProcurementPackage;
use Illuminate\Support\Facades\Gate;

class ProcurementPaymentController extends Controller
{
    public function show(Package $package)
    {
        Gate::authorize('view', $package);

        $procurementPackage = $package->procurementPackage;

        abort_if(!$procurementPackage, 404);

        // Tahap ini hanya bisa dibuka setelah pekerjaan dinyatakan selesai
        if (!in_array($procurementPackage->workflow_status, [
            ProcurementPackage::WORKFLOW_PAYMENT_PROCESS,
            ProcurementPackage::WORKFLOW_COMPLETED,
        ])) {
            return redirect()
                ->route('kabid.procurement-packages.execution.show', $package)
                ->with('error', 'Catat penyelesaian pekerjaan (BAST) terlebih dahulu.');
        }

        $process = $procurementPackage->procurementProcess;
        $payment = $procurementPackage->payment;

        abort_if(!$process || !$payment, 404);

        $procurementPackage->load(['package.fiscalYear', 'package.program']);

        return view('kabid.procurement-payments.show', compact('procurementPackage', 'process', 'payment'));
    }

    public function complete(Package $package)
    {
        Gate::authorize('view', $package);

        $procurementPackage = $package->procurementPackage;

        abort_if(!$procurementPackage, 404);

        abort_if(
            $procurementPackage->workflow_status !== ProcurementPackage::WORKFLOW_PAYMENT_PROCESS,
            403,
            'Paket tidak sedang berada di tahap Pembayaran.'
        );

        $procurementPackage->update([
            'status' => 'complete',
            'workflow_status' => ProcurementPackage::WORKFLOW_COMPLETED,
        ]);

        return redirect()
            ->route('kabid.procurement-packages.payment.show', $package)
            ->with('success', 'Seluruh proses pengadaan telah selesai. Kerja bagus!');
    }
}
