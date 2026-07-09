<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTravelSpjRequest;
use App\Models\Package;
use App\Models\TravelOrder;
use App\Models\TravelPersonnel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * Aksi SPJ SPD (simpan biaya rampung, ajukan, tarik).
 * Tampilannya menyatu di halaman detail SPPD (tab Laporan & Biaya).
 */
class TravelSpjController extends Controller
{
    public function store(StoreTravelSpjRequest $request, Package $package, TravelOrder $travelOrder): RedirectResponse
    {
        Gate::authorize('view', $package);
        abort_if((int) $travelOrder->package_id !== (int) $package->id, 404);

        if ($travelOrder->status !== TravelOrder::STATUS_APPROVED) {
            return redirect()
                ->route('staf.packages.travel-orders.show', [$package, $travelOrder])
                ->with('error', 'SPJ SPD hanya dapat disimpan setelah SPPD disetujui.');
        }

        if (!$travelOrder->isSpjEditable()) {
            return redirect()
                ->route('staf.packages.travel-orders.show', [$package, $travelOrder])
                ->with('error', 'SPJ yang sudah diajukan tidak dapat diubah.');
        }

        $payload = $request->validated('personnels');

        DB::transaction(function () use ($payload, $travelOrder): void {
            foreach ($payload as $personnelId => $values) {
                $personnel = TravelPersonnel::query()
                    ->where('travel_order_id', $travelOrder->id)
                    ->findOrFail($personnelId);

                $personnel->update([
                    'uang_harian' => $values['uang_harian'],
                    'biaya_transport' => $values['biaya_transport'],
                    'biaya_taksi' => $values['biaya_taksi'],
                    'biaya_penginapan' => $values['biaya_penginapan'],
                    'biaya_representasi' => $values['biaya_representasi'],
                    // Penanda pengeluaran riil (tanpa bukti / tidak menginap 30% SBU).
                    'transport_riil' => (bool) ($values['transport_riil'] ?? false),
                    'taksi_riil' => (bool) ($values['taksi_riil'] ?? false),
                    'penginapan_riil' => (bool) ($values['penginapan_riil'] ?? false),
                ]);
            }

            // Pastikan tercatat sebagai draf SPJ setelah biaya rampung diisi.
            if (blank($travelOrder->spj_status)) {
                $travelOrder->update(['spj_status' => TravelOrder::SPJ_DRAFT]);
            }
        });

        // "Simpan & Ajukan": langsung ajukan setelah biaya rampung tersimpan.
        if ($request->boolean('then_submit')) {
            $travelOrder->update([
                'spj_status' => TravelOrder::SPJ_SUBMITTED,
                'spj_submitted_at' => now(),
                'spj_catatan' => null,
                'spj_reviewed_at' => null,
                'spj_reviewed_by' => null,
            ]);
            $message = 'Biaya rampung tersimpan dan SPJ berhasil diajukan.';
        } else {
            $message = 'Biaya rampung SPJ berhasil disimpan.';
        }

        return redirect()
            ->route('staf.packages.travel-orders.show', [$package, $travelOrder])
            ->with('success', $message);
    }

    public function submit(Package $package, TravelOrder $travelOrder): RedirectResponse
    {
        Gate::authorize('view', $package);
        abort_if((int) $travelOrder->package_id !== (int) $package->id, 404);

        if ($travelOrder->status !== TravelOrder::STATUS_APPROVED) {
            return back()->with('error', 'SPPD belum disetujui.');
        }

        if (!in_array($travelOrder->spjStatus(), [TravelOrder::SPJ_DRAFT, TravelOrder::SPJ_REVISION], true)) {
            return back()->with('error', 'Hanya SPJ berstatus Draf atau Perlu Revisi yang dapat diajukan.');
        }

        $travelOrder->update([
            'spj_status' => TravelOrder::SPJ_SUBMITTED,
            'spj_submitted_at' => now(),
            'spj_catatan' => null,
            'spj_reviewed_at' => null,
            'spj_reviewed_by' => null,
        ]);

        return redirect()
            ->route('staf.packages.travel-orders.show', [$package, $travelOrder])
            ->with('success', 'SPJ SPD berhasil diajukan dan menunggu persetujuan.');
    }

    public function withdraw(Package $package, TravelOrder $travelOrder): RedirectResponse
    {
        Gate::authorize('view', $package);
        abort_if((int) $travelOrder->package_id !== (int) $package->id, 404);

        if ($travelOrder->spjStatus() !== TravelOrder::SPJ_SUBMITTED) {
            return back()->with('error', 'Hanya SPJ yang sedang Diajukan yang dapat ditarik kembali.');
        }

        $travelOrder->update([
            'spj_status' => TravelOrder::SPJ_DRAFT,
            'spj_submitted_at' => null,
        ]);

        return redirect()
            ->route('staf.packages.travel-orders.show', [$package, $travelOrder])
            ->with('success', 'Pengajuan SPJ ditarik kembali ke Draf.');
    }
}
