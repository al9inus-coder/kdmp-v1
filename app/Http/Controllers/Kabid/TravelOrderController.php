<?php

namespace App\Http\Controllers\Kabid;

use App\Http\Controllers\TravelOrderController as BaseTravelOrderController;
use App\Models\Package;
use App\Models\TravelOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TravelOrderController extends BaseTravelOrderController
{
    // Prefix role untuk view & route (kabid / staf). Dioverride oleh Staff\TravelOrderController.
    protected string $rolePrefix = 'kabid';

    // Bila true, travel order mengikuti alur pengajuan (draf -> diajukan -> disetujui/revisi/tolak).
    protected bool $submissionFlow = false;

    // Catatan: create/edit/store/update SPPD ada di Staff\TravelOrderController.
    // Kabid hanya meninjau (show + approve/revise/reject + review SPJ).

    public function show(Package $package, TravelOrder $travelOrder): View
    {
        Gate::authorize('view', $package);

        abort_if((int) $travelOrder->package_id !== (int) $package->id, 404);

        $package->load('account', 'program', 'activity', 'subActivity', 'fiscalYear');
        $travelOrder->load('personnels.employee', 'creator', 'reviewer', 'spjReviewer');

        $days = Carbon::parse($travelOrder->tanggal_berangkat)
            ->diffInDays(Carbon::parse($travelOrder->tanggal_kembali)) + 1;

        $estimates = [];
        foreach ($travelOrder->personnels as $personnel) {
            $estimates[$personnel->id] = $this->calculateEstimatedCost(
                $personnel->employee,
                $travelOrder,
                $days,
                $personnel->jenis_kendaraan
            );
        }

        return view($this->rolePrefix . '.travel-orders.show', compact('package', 'travelOrder', 'estimates'));
    }

    public function submit(Package $package, TravelOrder $travelOrder)
    {
        Gate::authorize('view', $package);

        abort_if((int) $travelOrder->package_id !== (int) $package->id, 404);

        if (!in_array($travelOrder->status, [TravelOrder::STATUS_DRAFT, TravelOrder::STATUS_REVISION], true)) {
            return back()->with('error', 'Hanya SPPD berstatus Draf atau Perlu Revisi yang dapat diajukan.');
        }

        if ($travelOrder->personnels()->count() === 0) {
            return back()->with('error', 'Lengkapi peserta perjalanan dinas sebelum diajukan.');
        }

        $travelOrder->update([
            'status' => TravelOrder::STATUS_SUBMITTED,
            'submitted_at' => now(),
            'catatan_review' => null,
            'reviewed_at' => null,
            'reviewed_by' => null,
        ]);

        return redirect()
            ->route($this->rolePrefix . '.packages.travel-orders.show', [$package, $travelOrder])
            ->with('success', 'SPPD berhasil diajukan dan menunggu persetujuan Kabid.');
    }

    public function withdraw(Package $package, TravelOrder $travelOrder)
    {
        Gate::authorize('view', $package);

        abort_if((int) $travelOrder->package_id !== (int) $package->id, 404);

        if ($travelOrder->status !== TravelOrder::STATUS_SUBMITTED) {
            return back()->with('error', 'Hanya SPPD yang sedang Diajukan yang dapat ditarik kembali.');
        }

        $travelOrder->update([
            'status' => TravelOrder::STATUS_DRAFT,
            'submitted_at' => null,
        ]);

        return redirect()
            ->route($this->rolePrefix . '.packages.travel-orders.show', [$package, $travelOrder])
            ->with('success', 'Pengajuan SPPD ditarik kembali ke Draf.');
    }

    /**
     * Review SPPD oleh Kabid (hanya saat berstatus Diajukan).
     */
    public function approve(Package $package, TravelOrder $travelOrder)
    {
        return $this->reviewTransition($package, $travelOrder, TravelOrder::STATUS_APPROVED, 'SPPD disetujui.');
    }

    public function revise(Request $request, Package $package, TravelOrder $travelOrder)
    {
        $request->validate(['catatan_review' => 'required|string|max:2000'], [
            'catatan_review.required' => 'Catatan revisi wajib diisi.',
        ]);

        return $this->reviewTransition($package, $travelOrder, TravelOrder::STATUS_REVISION,
            'SPPD dikembalikan untuk direvisi.', $request->input('catatan_review'));
    }

    public function reject(Request $request, Package $package, TravelOrder $travelOrder)
    {
        $request->validate(['catatan_review' => 'required|string|max:2000'], [
            'catatan_review.required' => 'Alasan penolakan wajib diisi.',
        ]);

        return $this->reviewTransition($package, $travelOrder, TravelOrder::STATUS_REJECTED,
            'SPPD ditolak.', $request->input('catatan_review'));
    }

    /**
     * Review SPJ (biaya rampung) oleh Kabid — hanya Setujui / Revisi (tanpa tolak).
     */
    public function approveSpj(Package $package, TravelOrder $travelOrder)
    {
        // Cegah persetujuan bila biaya rampung melebihi sisa anggaran perjalanan dinas (tekor).
        if ($this->spjMelebihiAnggaran($package, $travelOrder)) {
            return back()->with('error', 'SPJ tidak dapat disetujui: biaya rampung melebihi sisa anggaran perjalanan dinas. Minta revisi terlebih dahulu.');
        }

        return $this->spjReviewTransition($package, $travelOrder, TravelOrder::SPJ_APPROVED, 'SPJ SPD disetujui.');
    }

    /**
     * True bila menyetujui SPJ ini membuat realisasi perjalanan dinas melebihi pagu paket.
     */
    private function spjMelebihiAnggaran(Package $package, TravelOrder $travelOrder): bool
    {
        $sumBiaya = fn ($to) => $to->personnels->sum(fn ($p) => (float) $p->uang_harian
            + (float) $p->biaya_penginapan
            + (float) $p->biaya_representasi
            + (float) $p->biaya_transport
            + (float) ($p->biaya_taksi ?? 0));

        $realisasiLain = 0;
        foreach ($package->travelOrders()->with('personnels')->get() as $to) {
            if ((int) $to->id === (int) $travelOrder->id) {
                continue;
            }
            if ($to->spjStatus() !== TravelOrder::SPJ_APPROVED) {
                continue;
            }
            $realisasiLain += $sumBiaya($to);
        }

        $travelOrder->loadMissing('personnels');

        return ($realisasiLain + $sumBiaya($travelOrder)) > (float) $package->pagu;
    }

    public function reviseSpj(Request $request, Package $package, TravelOrder $travelOrder)
    {
        $request->validate(['spj_catatan' => 'required|string|max:2000'], [
            'spj_catatan.required' => 'Catatan revisi wajib diisi.',
        ]);

        return $this->spjReviewTransition($package, $travelOrder, TravelOrder::SPJ_REVISION,
            'SPJ dikembalikan untuk direvisi.', $request->input('spj_catatan'));
    }

    private function spjReviewTransition(Package $package, TravelOrder $travelOrder, string $status, string $message, ?string $catatan = null)
    {
        Gate::authorize('view', $package);

        abort_if((int) $travelOrder->package_id !== (int) $package->id, 404);

        if ($travelOrder->spjStatus() !== TravelOrder::SPJ_SUBMITTED) {
            return back()->with('error', 'Hanya SPJ yang sedang Diajukan yang dapat ditinjau.');
        }

        $travelOrder->update([
            'spj_status' => $status,
            'spj_catatan' => $catatan,
            'spj_reviewed_at' => now(),
            'spj_reviewed_by' => Auth::id(),
        ]);

        return redirect()
            ->route($this->rolePrefix . '.packages.travel-orders.show', [$package, $travelOrder])
            ->with('success', $message);
    }

    private function reviewTransition(Package $package, TravelOrder $travelOrder, string $status, string $message, ?string $catatan = null)
    {
        Gate::authorize('view', $package);

        abort_if((int) $travelOrder->package_id !== (int) $package->id, 404);

        if ($travelOrder->status !== TravelOrder::STATUS_SUBMITTED) {
            return back()->with('error', 'Hanya SPPD yang sedang Diajukan yang dapat ditinjau.');
        }

        $travelOrder->update([
            'status' => $status,
            'catatan_review' => $catatan,
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
        ]);

        return redirect()
            ->route($this->rolePrefix . '.packages.travel-orders.show', [$package, $travelOrder])
            ->with('success', $message);
    }

}
