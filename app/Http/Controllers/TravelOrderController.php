<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\SbuTransportRate;
use App\Models\Employee;

class TravelOrderController extends Controller
{
    public function create(\App\Models\Package $package)
    {
        $employees = \App\Models\Employee::orderBy('nama')->get();
        $dalamDaerahDestinations = \App\Models\SbuTransportRate::where('kategori', 'dalam_daerah')->select('tempat_tujuan')->distinct()->orderBy('tempat_tujuan')->pluck('tempat_tujuan');
        $luarDaerahKalbarDestinations = \App\Models\SbuTransportRate::where('kategori', 'luar_daerah')->pluck('tempat_tujuan');
        $luarDaerahLuarProvinsiDestinations = \App\Models\SbuUangHarian::select('provinsi')->where('provinsi', '!=', 'Kalimantan Barat')->distinct()->orderBy('provinsi')->pluck('provinsi');
        
        return view('travel-orders.create', compact('package', 'employees', 'dalamDaerahDestinations', 'luarDaerahKalbarDestinations', 'luarDaerahLuarProvinsiDestinations'));
    }

    public function store(Request $request, \App\Models\Package $package)
    {
        $validated = $request->validate([
            'tipe_perjalanan' => 'required|in:Dalam Daerah,Luar Daerah',
            'dasar_pelaksanaan' => 'nullable|string',
            'maksud_perjalanan' => 'required|string',
            'tempat_tujuan' => 'required|string',
            'tanggal_berangkat' => 'required|date',
            'tanggal_kembali' => 'required|date|after_or_equal:tanggal_berangkat',
            'tanggal_surat' => 'nullable|date',
            'employees' => 'required|array|min:1',
            'employees.*' => 'exists:employees,id',
            'kendaraan' => 'nullable|array',
            'kendaraan.*' => 'in:mobil,motor,pesawat,pengikut',
        ]);

        $travelOrder = $package->travelOrders()->create(\Illuminate\Support\Arr::except($validated, 'employees'));

        $days = Carbon::parse($travelOrder->tanggal_berangkat)->diffInDays(Carbon::parse($travelOrder->tanggal_kembali)) + 1;

        foreach ($validated['employees'] as $employeeId) {
            $employee = Employee::find($employeeId);
            $jenisKendaraan = $validated['kendaraan'][$employeeId] ?? 'mobil';
            $estimates = $this->calculateEstimatedCost($employee, $travelOrder, $days, $jenisKendaraan);

            $travelOrder->personnels()->create([
                'employee_id' => $employeeId,
                'jenis_kendaraan' => $jenisKendaraan,
                'uang_harian' => $estimates['uang_harian'],
                'biaya_penginapan' => $estimates['biaya_penginapan'],
                'biaya_representasi' => $estimates['biaya_representasi'],
                'biaya_transport' => $estimates['biaya_transport'],
            ]);
        }

        return redirect()->route('procurement-packages.show', $package)->with('success', 'Perjalanan dinas berhasil ditambahkan.');
    }

    public function show(\App\Models\Package $package, \App\Models\TravelOrder $travelOrder)
    {
        $travelOrder->load('personnels.employee');
        
        $days = \Carbon\Carbon::parse($travelOrder->tanggal_berangkat)->diffInDays(\Carbon\Carbon::parse($travelOrder->tanggal_kembali)) + 1;
        $estimates = [];
        foreach ($travelOrder->personnels as $personnel) {
            $estimates[$personnel->id] = $this->calculateEstimatedCost($personnel->employee, $travelOrder, $days, $personnel->jenis_kendaraan);
        }

        return view('travel-orders.show', compact('package', 'travelOrder', 'estimates'));
    }

    public function edit(\App\Models\Package $package, \App\Models\TravelOrder $travelOrder)
    {
        $travelOrder->load('personnels');
        $employees = \App\Models\Employee::orderBy('nama')->get();
        $selectedEmployees = $travelOrder->personnels->pluck('employee_id')->toArray();
        $dalamDaerahDestinations = \App\Models\SbuTransportRate::where('kategori', 'dalam_daerah')->select('tempat_tujuan')->distinct()->orderBy('tempat_tujuan')->pluck('tempat_tujuan');
        $luarDaerahKalbarDestinations = \App\Models\SbuTransportRate::where('kategori', 'luar_daerah')->pluck('tempat_tujuan');
        $luarDaerahLuarProvinsiDestinations = \App\Models\SbuUangHarian::select('provinsi')->where('provinsi', '!=', 'Kalimantan Barat')->distinct()->orderBy('provinsi')->pluck('provinsi');
        
        return view('travel-orders.edit', compact('package', 'travelOrder', 'employees', 'selectedEmployees', 'dalamDaerahDestinations', 'luarDaerahKalbarDestinations', 'luarDaerahLuarProvinsiDestinations'));
    }

    public function update(Request $request, \App\Models\Package $package, \App\Models\TravelOrder $travelOrder)
    {
        $validated = $request->validate([
            'tipe_perjalanan' => 'required|in:Dalam Daerah,Luar Daerah',
            'dasar_pelaksanaan' => 'nullable|string',
            'maksud_perjalanan' => 'required|string',
            'tempat_tujuan' => 'required|string',
            'tanggal_berangkat' => 'required|date',
            'tanggal_kembali' => 'required|date|after_or_equal:tanggal_berangkat',
            'tanggal_surat' => 'nullable|date',
            'employees' => 'required|array|min:1',
            'employees.*' => 'exists:employees,id',
            'kendaraan' => 'nullable|array',
            'kendaraan.*' => 'in:mobil,motor,pesawat,pengikut',
        ]);

        $travelOrder->update(\Illuminate\Support\Arr::except($validated, 'employees'));

        $days = Carbon::parse($travelOrder->tanggal_berangkat)->diffInDays(Carbon::parse($travelOrder->tanggal_kembali)) + 1;

        // Sync personnel
        $travelOrder->personnels()->whereNotIn('employee_id', $validated['employees'])->delete();
        $existingEmployees = $travelOrder->personnels()->pluck('employee_id')->toArray();
        $newEmployees = array_diff($validated['employees'], $existingEmployees);

        foreach ($newEmployees as $employeeId) {
            $employee = Employee::find($employeeId);
            $jenisKendaraan = $validated['kendaraan'][$employeeId] ?? 'mobil';
            $estimates = $this->calculateEstimatedCost($employee, $travelOrder, $days, $jenisKendaraan);

            $travelOrder->personnels()->create([
                'employee_id' => $employeeId,
                'jenis_kendaraan' => $jenisKendaraan,
                'uang_harian' => $estimates['uang_harian'],
                'biaya_penginapan' => $estimates['biaya_penginapan'],
                'biaya_representasi' => $estimates['biaya_representasi'],
                'biaya_transport' => $estimates['biaya_transport'],
                'biaya_taksi' => $estimates['biaya_taksi'] ?? 0,
            ]);
        }

        // Update existing personnels if needed
        foreach ($existingEmployees as $employeeId) {
            if (in_array($employeeId, $validated['employees'])) {
                $employee = Employee::find($employeeId);
                $jenisKendaraan = $validated['kendaraan'][$employeeId] ?? 'mobil';
                // Recalculate estimates in case vehicle or destination changed
                $estimates = $this->calculateEstimatedCost($employee, $travelOrder, $days, $jenisKendaraan);
                
                $travelOrder->personnels()->where('employee_id', $employeeId)->update([
                    'jenis_kendaraan' => $jenisKendaraan,
                    'uang_harian' => $estimates['uang_harian'],
                    'biaya_penginapan' => $estimates['biaya_penginapan'],
                    'biaya_representasi' => $estimates['biaya_representasi'],
                    'biaya_transport' => $estimates['biaya_transport'],
                    'biaya_taksi' => $estimates['biaya_taksi'] ?? 0,
                ]);
            }
        }

        return redirect()->route('procurement-packages.show', $package)->with('success', 'Perjalanan dinas berhasil diperbarui.');
    }

    public function destroy(\App\Models\Package $package, \App\Models\TravelOrder $travelOrder)
    {
        $travelOrder->delete();
        return redirect()->route('procurement-packages.show', $package)->with('success', 'Perjalanan dinas berhasil dihapus.');
    }

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
