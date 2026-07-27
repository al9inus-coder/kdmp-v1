<?php

namespace App\Http\Controllers\Kabid;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\ProcurementPackage;
use App\Models\Skpd;
use App\Services\Pengadaan\KelengkapanTahap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProcurementPaymentController extends Controller
{
    public function __construct(private KelengkapanTahap $kelengkapan)
    {
    }

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

        // Prefill PPTK dari master SKPD selama belum pernah diisi.
        $pptkPrefill = [
            'nama_pptk' => $payment->nama_pptk,
            'nip_pptk' => $payment->nip_pptk,
            'pangkat_golongan_pptk' => $payment->pangkat_golongan_pptk,
        ];

        if (blank($payment->nama_pptk) && ($skpd = Skpd::first())) {
            $pptkPrefill = [
                'nama_pptk' => $skpd->nama_pptk,
                'nip_pptk' => $skpd->nip_pptk,
                'pangkat_golongan_pptk' => $skpd->pangkat_pptk,
            ];
        }

        $kurang = $this->kelengkapan->pembayaran($process, $payment);

        // Belum pernah disimpan → sambut dengan form isian, bukan pratinjau
        // dokumen yang memang belum bisa dirender.
        $pernahDiisi = $this->kelengkapan->pembayaranPernahDiisi($process, $payment);

        return view('kabid.procurement-payments.show', compact(
            'procurementPackage', 'process', 'payment', 'pptkPrefill', 'kurang', 'pernahDiisi'
        ));
    }

    /**
     * Menyimpan seluruh data penagihan dan setoran penyedia.
     *
     * NPWP, nama bank, dan nomor rekening menempel pada procurement_processes
     * (tempat data penyedia berada), tetapi diisi di sini karena hanya
     * dokumen tahap inilah yang memakainya.
     */
    public function store(Request $request, Package $package)
    {
        Gate::authorize('view', $package);

        $procurementPackage = $package->procurementPackage;

        abort_if(! $procurementPackage, 404);

        abort_if(
            $procurementPackage->workflow_status !== ProcurementPackage::WORKFLOW_PAYMENT_PROCESS,
            403,
            'Data pembayaran hanya bisa diubah selama paket berada di tahap Pembayaran.'
        );

        $data = $request->validate([
            'nomor_invoice' => 'nullable|string|max:255',
            'tanggal_invoice' => 'nullable|date',
            'nomor_bap' => 'nullable|string|max:255',
            'tanggal_bap' => 'nullable|date',
            'nomor_kwitansi' => 'nullable|string|max:255',
            'tanggal_kwitansi' => 'nullable|date',
            'tanggal_ringkasan_kontrak' => 'nullable|date',
            'is_non_pkp' => 'nullable|boolean',
            'tanggal_non_pkp' => 'required_if:is_non_pkp,1|nullable|date',
            'nama_pptk' => 'nullable|string|max:255',
            'nip_pptk' => 'nullable|string|max:255',
            'pangkat_golongan_pptk' => 'nullable|string|max:255',
            'npwp_penyedia' => 'nullable|string|max:255',
            'nama_bank' => 'nullable|string|max:255',
            'nomor_rekening' => 'nullable|string|max:255',
        ], [
            'tanggal_non_pkp.required_if' => 'Tanggal surat Non-PKP wajib diisi bila suratnya dilampirkan.',
        ]);

        $data['is_non_pkp'] = $request->boolean('is_non_pkp');

        // Tiga kolom penyedia tinggal di tabel proses; sisanya di pembayaran.
        $penyedia = array_intersect_key($data, array_flip(['npwp_penyedia', 'nama_bank', 'nomor_rekening']));
        $penagihan = array_diff_key($data, $penyedia);

        $procurementPackage->procurementProcess?->update($penyedia);

        $procurementPackage->payment()->updateOrCreate(
            ['procurement_package_id' => $procurementPackage->id],
            $penagihan
        );

        return redirect()
            ->route('kabid.procurement-packages.payment.show', $package)
            ->with('success', 'Data pembayaran tersimpan.');
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

        // Penjagaan di server, bukan sekadar tombol yang diredupkan: pengadaan
        // tidak boleh ditutup selagi dokumen pembayarannya belum lengkap.
        $kurang = $this->kelengkapan->pembayaran(
            $procurementPackage->procurementProcess,
            $procurementPackage->payment
        );

        if ($kurang !== []) {
            return redirect()
                ->route('kabid.procurement-packages.payment.show', $package)
                ->with('error', 'Belum bisa diselesaikan. Masih kosong: ' . $this->kelengkapan->kalimat($kurang) . '.');
        }

        $procurementPackage->update([
            'status' => 'complete',
            'workflow_status' => ProcurementPackage::WORKFLOW_COMPLETED,
        ]);

        return redirect()
            ->route('kabid.procurement-packages.payment.show', $package)
            ->with('success', 'Seluruh proses pengadaan telah selesai. Kerja bagus!');
    }
}
