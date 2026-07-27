<?php

namespace App\Http\Controllers\Kabid;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\ProcurementPackage;
use App\Services\Pengadaan\KelengkapanTahap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProcurementProcessController extends Controller
{
    public function __construct(private KelengkapanTahap $kelengkapan)
    {
    }

    public function show(Package $package)
    {
        Gate::authorize('view', $package);

        $procurementPackage = $package->procurementPackage;

        abort_if(!$procurementPackage, 404);

        // Tahap ini hanya bisa dibuka setelah persiapan diselesaikan
        if ($procurementPackage->workflow_status === ProcurementPackage::WORKFLOW_DRAFT) {
            return redirect()
                ->route('kabid.procurement-packages.show', $package)
                ->with('error', 'Selesaikan tahap Persiapan Pengadaan terlebih dahulu.')
                ->with('panel', 6);
        }

        $process = $procurementPackage->procurementProcess()->firstOrCreate([
            'procurement_package_id' => $procurementPackage->id,
        ], [
            'nama_penyedia' => $procurementPackage->procurementRequest?->nama_penyedia,
        ]);

        $procurementPackage->load([
            'package.fiscalYear',
            'package.program',
            'package.activity',
            'package.subActivity',
            'package.account',
            'technicalSpecification.items',
            'procurementRequest',
            'priceReferences',
        ]);

        return view('kabid.procurement-processes.show', compact('procurementPackage', 'process'));
    }

    /**
     * Data pemilihan terkunci setelah paket masuk tahap Pelaksanaan;
     * hanya Admin yang dapat membukanya kembali.
     */
    private function assertSelectionEditable(?ProcurementPackage $procurementPackage): void
    {
        abort_if(!$procurementPackage, 404);

        abort_if(
            in_array($procurementPackage->workflow_status, [
                ProcurementPackage::WORKFLOW_EXECUTION,
                ProcurementPackage::WORKFLOW_PAYMENT_PROCESS,
                ProcurementPackage::WORKFLOW_COMPLETED,
            ]),
            403,
            'Tahap pemilihan penyedia sudah diselesaikan dan terkunci.'
        );
    }

    public function updateOrder(Request $request, Package $package)
    {
        Gate::authorize('view', $package);

        $procurementPackage = $package->procurementPackage;

        $this->assertSelectionEditable($procurementPackage);

        $process = $procurementPackage->procurementProcess;

        abort_if(!$process, 404);

        $data = $request->validate([
            'nomor_surat_pesanan' => 'nullable|string|max:255',
            'tanggal_surat_pesanan' => 'nullable|date',
            'tanggal_barang_diterima' => 'nullable|date|after_or_equal:tanggal_surat_pesanan',
            'nilai_kontrak' => 'nullable|numeric|min:0',
            'catatan' => 'nullable|string',
        ]);

        $process->update($data);

        return redirect()
            ->route('kabid.procurement-packages.procurement-process.show', $package)
            ->with('success', 'Data surat pesanan berhasil disimpan.')
            ->with('panel', 1);
    }

    public function updateVendor(Request $request, Package $package)
    {
        Gate::authorize('view', $package);

        $procurementPackage = $package->procurementPackage;

        $this->assertSelectionEditable($procurementPackage);

        $process = $procurementPackage->procurementProcess;

        abort_if(!$process, 404);

        // NPWP, nama bank, dan nomor rekening pindah ke tahap Pembayaran —
        // ketiganya hanya dipakai dokumen di tahap itu.
        $data = $request->validate([
            'nama_penyedia' => 'nullable|string|max:255',
            'alamat_penyedia' => 'nullable|string',
            'nama_pic' => 'nullable|string|max:255',
            'jabatan_pic' => 'nullable|string|max:255',
        ]);

        $process->update($data);

        return redirect()
            ->route('kabid.procurement-packages.procurement-process.show', $package)
            ->with('success', 'Data penyedia berhasil disimpan.')
            ->with('panel', 2);
    }

    public function startExecution(Package $package)
    {
        Gate::authorize('view', $package);

        $procurementPackage = $package->procurementPackage;

        $this->assertSelectionEditable($procurementPackage);

        $process = $procurementPackage->procurementProcess;

        abort_if(!$process, 404);

        $kurang = $this->kelengkapan->pemilihan($process);

        if ($kurang !== []) {
            return redirect()
                ->route('kabid.procurement-packages.procurement-process.show', $package)
                ->with('error', 'Belum lengkap: ' . $this->kelengkapan->kalimat($kurang) . '.')
                ->with('panel', 4);
        }

        $procurementPackage->update([
            'workflow_status' => ProcurementPackage::WORKFLOW_EXECUTION,
        ]);

        return redirect()
            ->route('kabid.procurement-packages.procurement-process.show', $package)
            ->with('success', 'Pemilihan penyedia selesai. Paket masuk tahap Pelaksanaan Kontrak.')
            ->with('panel', 4);
    }
}
