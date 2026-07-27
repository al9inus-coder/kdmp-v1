<?php

namespace App\Http\Controllers\Kabid;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\ProcurementPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProcurementExecutionController extends Controller
{
    /**
     * Controller ini dipakai route kabid DAN admin (aksi addendum/finish) —
     * redirect harus mengikuti prefix role user, bukan hardcode kabid.
     */
    private function rolePrefix(): string
    {
        return auth()->user()->hasAnyRole(['Admin', 'Super Admin']) ? 'admin' : 'kabid';
    }

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
                ->route($this->rolePrefix() . '.procurement-packages.procurement-process.show', $package)
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

        // Data PPTK kini diisi di tahap Pembayaran, bukan di sini.
        return view('kabid.procurement-executions.show', compact('procurementPackage', 'process', 'payment'));
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
            ->route($this->rolePrefix() . '.procurement-packages.execution.show', $package)
            ->with('success', 'Adendum tersimpan — batas akhir kontrak diperbarui.');
    }

    public function finishWork(Request $request, Package $package)
    {
        Gate::authorize('view', $package);

        $procurementPackage = $package->procurementPackage;

        $this->assertExecutionEditable($procurementPackage);

        // Tahap ini hanya mencatat serah terima pekerjaan. Invoice, BAP,
        // kwitansi, PPTK, dan data setoran penyedia diisi di tahap Pembayaran
        // — dokumennya memang belum tentu terbit saat pekerjaan baru selesai.
        $data = $request->validate([
            'nomor_bast' => 'required|string|max:255',
            'tanggal_bast' => 'required|date',
        ]);

        $procurementPackage->payment()->updateOrCreate(
            ['procurement_package_id' => $procurementPackage->id],
            $data
        );

        $procurementPackage->update([
            'workflow_status' => ProcurementPackage::WORKFLOW_PAYMENT_PROCESS,
        ]);

        return redirect()
            ->route($this->rolePrefix() . '.procurement-packages.execution.show', $package)
            ->with('success', 'Pekerjaan selesai dicatat. Paket masuk tahap Pembayaran.');
    }
}
