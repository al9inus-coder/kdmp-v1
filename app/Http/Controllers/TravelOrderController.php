<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\SbuTransportRate;
use App\Models\Employee;

class TravelOrderController extends Controller
{
    public function updateBiaya(Request $request, \App\Models\Package $package, \App\Models\TravelOrder $travelOrder, \App\Models\TravelPersonnel $personnel)
    {
        $validated = $request->validate([
            'uang_harian' => 'required|numeric|min:0',
            'biaya_penginapan' => 'required|numeric|min:0',
            'biaya_representasi' => 'required|numeric|min:0',
            'biaya_transport' => 'required|numeric|min:0',
            'biaya_taksi' => 'required|numeric|min:0',
        ]);

        $personnel->update($validated);

        return back()->with('success', 'Biaya rampung berhasil diperbarui.');
    }

    protected function calculateEstimatedCost($employee, $travelOrder, $days, $jenisKendaraan = 'mobil')
    {
        \Log::info('calculateEstimatedCost CALLED', [
            'employee_id' => $employee->id,
            'travelOrder_tipe' => $travelOrder->tipe_perjalanan,
            'travelOrder_tgl_berangkat' => $travelOrder->tanggal_berangkat,
            'travelOrder_tgl_kembali' => $travelOrder->tanggal_kembali,
            'days' => $days,
            'jenis_kendaraan' => $jenisKendaraan
        ]);

        $personnel = new \App\Models\TravelPersonnel();
        $personnel->employee_id = $employee->id;
        $personnel->travel_order_id = $travelOrder->id;
        $personnel->jenis_kendaraan = $jenisKendaraan;
        $personnel->setRelation('employee', $employee);
        $personnel->setRelation('travelOrder', $travelOrder);

        return $personnel->getEstimatedCosts();
    }
}
