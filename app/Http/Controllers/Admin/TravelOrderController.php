<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\TravelOrder;
use App\Models\TravelPersonnel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Koreksi Admin: memperbaiki biaya rampung SPJ pada SPPD yang sudah disetujui
 * (human error), tanpa mengubah status, pelaksana, atau data inti SPPD.
 */
class TravelOrderController extends Controller
{
    /**
     * Detail SPPD untuk Admin. Aksi peninjauan tetap khusus Kabid,
     * sedangkan Admin dapat mengakses koreksi biaya rampung dari halaman ini.
     */
    public function show(Package $package, TravelOrder $travelOrder): View
    {
        abort_if((int) $travelOrder->package_id !== (int) $package->id, 404);

        $package->load('account', 'program', 'activity', 'subActivity', 'fiscalYear');
        $travelOrder->load('personnels.employee', 'creator', 'reviewer', 'spjReviewer', 'spjKoreksiBy');

        $estimates = [];
        foreach ($travelOrder->personnels as $personnel) {
            $estimates[$personnel->id] = $personnel->getEstimatedCosts();
        }

        return view('kabid.travel-orders.show', compact('package', 'travelOrder', 'estimates'));
    }

    public function editBiaya(Package $package, TravelOrder $travelOrder)
    {
        abort_if((int) $travelOrder->package_id !== (int) $package->id, 404);

        // Koreksi hanya relevan bila biaya rampung sudah pernah diisi.
        abort_if($travelOrder->spjStatus() === TravelOrder::SPJ_DRAFT, 404, 'Belum ada biaya rampung untuk dikoreksi.');

        $package->load('account', 'subActivity', 'fiscalYear');
        $travelOrder->load('personnels.employee', 'spjKoreksiBy');

        $estimates = [];
        foreach ($travelOrder->personnels as $personnel) {
            $estimates[$personnel->id] = $personnel->getEstimatedCosts();
        }

        return view('admin.travel-orders.koreksi-biaya', compact('package', 'travelOrder', 'estimates'));
    }

    public function updateBiaya(Request $request, Package $package, TravelOrder $travelOrder): RedirectResponse
    {
        abort_if((int) $travelOrder->package_id !== (int) $package->id, 404);
        abort_if($travelOrder->spjStatus() === TravelOrder::SPJ_DRAFT, 404);

        $validated = $request->validate([
            'personnels' => ['required', 'array', 'min:1'],
            'personnels.*.uang_harian' => ['required', 'numeric', 'min:0'],
            'personnels.*.biaya_transport' => ['required', 'numeric', 'min:0'],
            'personnels.*.biaya_taksi' => ['required', 'numeric', 'min:0'],
            'personnels.*.biaya_penginapan' => ['required', 'numeric', 'min:0'],
            'personnels.*.biaya_representasi' => ['required', 'numeric', 'min:0'],
            'personnels.*.transport_riil' => ['nullable', 'boolean'],
            'personnels.*.taksi_riil' => ['nullable', 'boolean'],
            'personnels.*.penginapan_riil' => ['nullable', 'boolean'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated, $travelOrder) {
            foreach ($validated['personnels'] as $personnelId => $values) {
                // Hanya pelaksana milik SPPD ini — tidak menambah/menghapus pelaksana.
                $personnel = TravelPersonnel::query()
                    ->where('travel_order_id', $travelOrder->id)
                    ->find($personnelId);

                if (!$personnel) {
                    continue;
                }

                $personnel->update([
                    'uang_harian' => $values['uang_harian'],
                    'biaya_transport' => $values['biaya_transport'],
                    'biaya_taksi' => $values['biaya_taksi'],
                    'biaya_penginapan' => $values['biaya_penginapan'],
                    'biaya_representasi' => $values['biaya_representasi'],
                    'transport_riil' => (bool) ($values['transport_riil'] ?? false),
                    'taksi_riil' => (bool) ($values['taksi_riil'] ?? false),
                    'penginapan_riil' => (bool) ($values['penginapan_riil'] ?? false),
                ]);
            }

            // Jejak audit — status & spj_status TIDAK diubah.
            $travelOrder->update([
                'spj_koreksi_by' => Auth::id(),
                'spj_koreksi_at' => now(),
                'spj_koreksi_catatan' => $validated['catatan'] ?? null,
            ]);
        });

        return redirect()
            ->route('admin.packages.travel-orders.show', [$package, $travelOrder])
            ->with('success', 'Biaya rampung berhasil dikoreksi. Status SPPD tidak berubah.');
    }
}
