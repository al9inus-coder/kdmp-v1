<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\ProcurementPackage;
use App\Models\AiPrompt;

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
            'package.travelOrders.personnels.employee',
            'creator',
            'technicalSpecification.items',
            'procurementRequest',
            'priceReferences',
            'externalRecords',
        ]);

        $procurementPackage->loadCount('priceReferences');

        $procurementPackage->syncPpkFromSkpd();

        $aiPrompt = AiPrompt::where('code', 'technical_specification')
            ->where('is_active', true)
            ->first();

        if ($this->isTravelSwakelolaPackage($procurementPackage->package)) {
            $travelStats = $this->buildTravelStats($procurementPackage->package);

            return view('kabid.procurement-packages.show-swakelola-travel', compact(
                'procurementPackage',
                'aiPrompt',
                'travelStats'
            ));
        }

        if ($this->isLemburSwakelolaPackage($procurementPackage->package)) {
            $lemburStats = $this->buildLemburStats($procurementPackage->package);

            return view('kabid.procurement-packages.show-swakelola-lembur', compact(
                'procurementPackage',
                'aiPrompt',
                'lemburStats'
            ));
        }

        if ($procurementPackage->package->metode_pengadaan === 'Dikecualikan') {
            return view('kabid.procurement-packages.show-dikecualikan', compact('procurementPackage'));
        }

        return view('admin.procurement-packages.show', compact('procurementPackage', 'aiPrompt'));
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

    private function isTravelSwakelolaPackage(Package $package): bool
    {
        $jenisPengadaan = str($package->jenis_pengadaan ?? '')->lower();
        $accountName = str($package->account?->nama ?? '')->lower();

        return $jenisPengadaan->contains('swakelola')
            && $accountName->contains('perjalanan dinas');
    }

    private function isLemburSwakelolaPackage(Package $package): bool
    {
        $jenisPengadaan = str($package->jenis_pengadaan ?? '')->lower();
        $accountName = str($package->account?->nama ?? '')->lower();

        return $jenisPengadaan->contains('swakelola')
            && $accountName->contains('lembur');
    }

    private function buildLemburStats(Package $package): array
    {
        $sbuRates = \App\Models\SbuLembur::all();
        $overtimes = \App\Models\Overtime::where('package_id', $package->id)
            ->with('details.employee')
            ->get();

        $months = [];
        $totalRealisasi = 0.0;
        $bulanTerisi = 0;

        for ($num = 1; $num <= 12; $num++) {
            $overtime = $overtimes->firstWhere('bulan', $num);
            $total = 0.0;

            if ($overtime) {
                $total = (float) $overtime->calculateTotalRealisasi($sbuRates);
                $totalRealisasi += $total;
                $bulanTerisi++;
            }

            $months[$num] = [
                'exists' => (bool) $overtime,
                'total' => $total,
                'is_locked' => $overtime ? (bool) $overtime->is_locked : false,
            ];
        }

        $pagu = (float) ($package->pagu ?? 0);
        $percentage = $pagu > 0 ? min(100, ($totalRealisasi / $pagu) * 100) : 0;

        return [
            'pagu' => $pagu,
            'total_realisasi' => $totalRealisasi,
            'sisa_anggaran' => $pagu - $totalRealisasi,
            'percentage' => $percentage,
            'bulan_terisi' => $bulanTerisi,
            'months' => $months,
        ];
    }

    private function buildTravelStats(Package $package): array
    {
        $travelOrders = $package->travelOrders
            ->filter(fn ($travelOrder) => $travelOrder->spjStatus() === \App\Models\TravelOrder::SPJ_APPROVED);

        $totalRealisasi = $travelOrders->sum(function ($travelOrder) {
            return $travelOrder->personnels->sum(function ($personnel) {
                return (float) $personnel->uang_harian
                    + (float) $personnel->biaya_transport
                    + (float) ($personnel->biaya_taksi ?? 0)
                    + (float) $personnel->biaya_penginapan
                    + (float) $personnel->biaya_representasi;
            });
        });

        $pagu = (float) ($package->pagu ?? 0);
        $percentage = $pagu > 0 ? min(100, ($totalRealisasi / $pagu) * 100) : 0;

        return [
            'pagu' => $pagu,
            'total_realisasi' => $totalRealisasi,
            'sisa_anggaran' => $pagu - $totalRealisasi,
            'percentage' => $percentage,
            'total_orders' => $travelOrders->count(),
            'total_personnels' => $travelOrders->sum(fn ($to) => $to->personnels->count()),
        ];
    }
}
