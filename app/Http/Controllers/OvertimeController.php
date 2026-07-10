<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Overtime;
use App\Models\OvertimeDetail;
use App\Models\Employee;
use Illuminate\Http\Request;

class OvertimeController extends Controller
{
    public function index(Package $package)
    {
        // View to show 12 months cards
        return view('overtimes.index', compact('package'));
    }

    public function show(Package $package, $month)
    {
        $year = date('Y'); // fallback if package doesn't have year
        // We can use package created_at year or a specific field if it exists. Let's assume current year or package created_at.
        $year = $package->created_at ? $package->created_at->format('Y') : date('Y');

        $overtime = Overtime::firstOrCreate([
            'package_id' => $package->id,
            'bulan' => $month,
            'tahun' => $year
        ]);

        if ($overtime->details()->count() == 0) {
            $employees = Employee::all();
            foreach ($employees as $employee) {
                OvertimeDetail::create([
                    'overtime_id' => $overtime->id,
                    'employee_id' => $employee->id,
                    'daily_hours' => [],
                    'use_uang_makan' => false
                ]);
            }
        }

        // We also need SBU Lembur rates
        $sbuRates = \App\Models\SbuLembur::all();
        
        // Ambil hari libur dari database (tabel holidays)
        $holidaysDataFull = [];
        $dbHolidays = \App\Models\Holiday::whereYear('holiday_date', $year)->get();
        foreach($dbHolidays as $h) {
            $holidaysDataFull[] = [
                'date' => $h->holiday_date,
                'description' => $h->description
            ];
        }
        
        // Pass to view for calendar
        $overtime->load('details.employee');
        
        return view('overtimes.show_calendar', compact('package', 'overtime', 'month', 'year', 'sbuRates', 'holidaysDataFull'));
    }

    public function resetMonth(Request $request, Package $package, Overtime $overtime)
    {
        if ($overtime->is_locked) {
            return response()->json(['success' => false, 'message' => 'Data sudah dikunci.']);
        }
        // Reset all daily_hours to empty array for this month
        $details = OvertimeDetail::where('overtime_id', $overtime->id)->get();
        foreach($details as $detail) {
            $detail->daily_hours = [];
            $detail->use_uang_makan = false;
            $detail->save();
        }
        return response()->json(['success' => true]);
    }

    public function updateAjax(Request $request, Package $package, Overtime $overtime)
    {
        if ($overtime->is_locked) {
            return response()->json(['success' => false, 'message' => 'Data sudah dikunci.']);
        }

        $employeeId = $request->input('employee_id');
        $dateStr = $request->input('date'); // YYYY-MM-DD
        $hours = (int) $request->input('hours', 0);
        $useUangMakan = filter_var($request->input('use_uang_makan', false), FILTER_VALIDATE_BOOLEAN);
        $action = $request->input('action', 'update');
        
        if($action === 'save_dasar') {
            $overtime->dasar_pelaksanaan = $request->input('dasar_pelaksanaan');
            $overtime->save();
            return response()->json(['success' => true]);
        }
        
        $day = (int) date('j', strtotime($dateStr));
        
        $detail = OvertimeDetail::where('overtime_id', $overtime->id)
                    ->where('employee_id', $employeeId)
                    ->first();
                    
        if(!$detail) {
            return response()->json(['success' => false, 'message' => 'Pegawai tidak ditemukan di paket ini']);
        }
        
        // Cek bentrok dengan Perjalanan Dinas jika input jam > 0
        if($action === 'update' && $hours > 0) {
            $isOnTravel = \App\Models\TravelPersonnel::where('employee_id', $employeeId)
                ->whereHas('travelOrder', function($q) use ($dateStr) {
                    $q->where('tanggal_berangkat', '<=', $dateStr)
                      ->where('tanggal_kembali', '>=', $dateStr);
                })->exists();
                
            if($isOnTravel) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Pegawai tidak bisa lembur karena sedang melaksanakan Perjalanan Dinas pada tanggal tersebut.'
                ], 422);
            }
        }
        
        $dailyHours = $detail->daily_hours ?? [];
        
        if($action === 'delete') {
            unset($dailyHours[$day]);
        } else {
            $dailyHours[$day] = $hours;
            $detail->use_uang_makan = $useUangMakan; // apply to entire month
        }
        
        $detail->daily_hours = $dailyHours;
        $detail->save();
        
        return response()->json(['success' => true]);
    }

    public function autoFill(Request $request, Package $package, Overtime $overtime)
    {
        if ($overtime->is_locked) {
            return response()->json(['success' => false, 'message' => 'Data sudah dikunci.']);
        }

        // Auto fill for selected employees
        $employeeIds = $request->input('employee_ids', []);
        $holidays = $request->input('holidays', []); // Array of date strings 'YYYY-MM-DD'
        
        $year = $overtime->tahun;
        $month = $overtime->bulan;
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        
        // Cek data Perjalanan Dinas untuk pegawai-pegawai ini di bulan tersebut
        $travels = \App\Models\TravelPersonnel::whereIn('employee_id', $employeeIds)
            ->whereHas('travelOrder', function($q) use ($year, $month, $daysInMonth) {
                $q->where(function($q2) use ($year, $month) {
                    $q2->whereMonth('tanggal_berangkat', $month)
                       ->whereYear('tanggal_berangkat', $year);
                })->orWhere(function($q2) use ($year, $month) {
                    $q2->whereMonth('tanggal_kembali', $month)
                       ->whereYear('tanggal_kembali', $year);
                })->orWhere(function($q2) use ($year, $month, $daysInMonth) {
                    // Atau perjalanannya melintasi bulan ini (berangkat bulan lalu, kembali bulan depan)
                    $q2->where('tanggal_berangkat', '<', "$year-$month-01")
                       ->where('tanggal_kembali', '>', "$year-$month-$daysInMonth");
                });
            })->with('travelOrder')->get();

        $travelDates = [];
        foreach($travels as $t) {
            $empId = $t->employee_id;
            if(!isset($travelDates[$empId])) {
                $travelDates[$empId] = [];
            }
            
            $start = \Carbon\Carbon::parse($t->travelOrder->tanggal_berangkat);
            $end = \Carbon\Carbon::parse($t->travelOrder->tanggal_kembali);
            
            for($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $travelDates[$empId][] = $date->format('Y-m-d');
            }
        }
        
        foreach($employeeIds as $empId) {
            $detail = OvertimeDetail::where('overtime_id', $overtime->id)
                        ->where('employee_id', $empId)
                        ->first();
                        
            if($detail) {
                $dailyHours = $detail->daily_hours ?? [];
                
                for($d = 1; $d <= $daysInMonth; $d++) {
                    $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
                    
                    // Jika tanggal ini ada di daftar dinas luar pegawai, lewati (kosongkan)
                    if(isset($travelDates[$empId]) && in_array($dateStr, $travelDates[$empId])) {
                        unset($dailyHours[$d]);
                        continue;
                    }
                    
                    $dayOfWeek = date('N', strtotime($dateStr));
                    
                    $isWeekend = ($dayOfWeek == 6 || $dayOfWeek == 7);
                    $isHoliday = in_array($dateStr, $holidays);
                    
                    if($isWeekend || $isHoliday) {
                        $dailyHours[$d] = 5;
                    } else {
                        $dailyHours[$d] = 2;
                    }
                }
                
                $detail->daily_hours = $dailyHours;
                $detail->save();
            }
        }
        
        return response()->json(['success' => true]);
    }

    public function update(Request $request, Package $package, Overtime $overtime)
    {
        if ($overtime->is_locked) {
            return redirect()->back()->with('error', 'Data sudah dikunci.');
        }

        $details = $request->input('details', []);
        
        foreach ($details as $detailId => $data) {
            $detail = OvertimeDetail::find($detailId);
            if ($detail && $detail->overtime_id == $overtime->id) {
                $detail->update([
                    'daily_hours' => $data['daily_hours'] ?? [],
                    'use_uang_makan' => isset($data['use_uang_makan']) ? (bool) $data['use_uang_makan'] : false,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Data lembur berhasil disimpan.');
    }

    public function print(Package $package, Overtime $overtime, $type)
    {
        $sbuRates = \App\Models\SbuLembur::all();
        
        $year = $overtime->tahun;
        $month = $overtime->bulan;
        $holidays = \App\Models\Holiday::whereYear('holiday_date', $year)
                                       ->whereMonth('holiday_date', $month)
                                       ->pluck('holiday_date')
                                       ->toArray();
        
        $overtime->load('details.employee');
        $skpd = \App\Models\Skpd::first();
        
        if ($type == 'rekap') {
            return view('overtimes.print_rekap', compact('package', 'overtime', 'sbuRates', 'holidays', 'skpd'));
        } elseif ($type == 'tanda_terima') {
            return view('overtimes.print_tanda_terima', compact('package', 'overtime', 'sbuRates', 'skpd'));
        } elseif ($type == 'kwitansi') {
            return view('overtimes.print_kwitansi', compact('package', 'overtime', 'sbuRates', 'skpd'));
        }

        return abort(404);
    }

    public function updateRates(Request $request, Package $package, Overtime $overtime, OvertimeDetail $detail)
    {
        if ($overtime->is_locked) {
            return redirect()->back()->with('error', 'Data lembur sudah dikunci.');
        }

        $request->validate([
            'rate_lembur_fix' => 'nullable|numeric|min:0',
            'rate_makan_fix' => 'nullable|numeric|min:0',
        ]);

        $detail->update([
            'rate_lembur_fix' => $request->rate_lembur_fix,
            'rate_makan_fix' => $request->rate_makan_fix,
        ]);

        return redirect()->back()->with('success', 'Standar Biaya (SBU) berhasil diperbarui.');
    }



    public function lock(Request $request, Package $package, $month)
    {
        $year = $package->created_at ? $package->created_at->format('Y') : date('Y');
        $overtime = Overtime::where('package_id', $package->id)
            ->where('bulan', $month)
            ->where('tahun', $year)
            ->firstOrFail();

        $userRole = auth()->user()->getRoleNames()->first() ?? '';
        if (in_array($userRole, ['Admin', 'Kabid'])) {
            $sbuRates = \App\Models\SbuLembur::all();
            
            foreach ($overtime->details as $detail) {
                $golongan = $detail->employee->golongan ?? null;

                $updateData = [];
                $updateData['golongan_fix'] = $golongan ?? '-';

                if (is_null($detail->rate_lembur_fix)) {
                    $updateData['rate_lembur_fix'] = \App\Models\SbuLembur::pickRate($sbuRates, 'Uang Lembur', $golongan)?->besaran ?? 0;
                }

                if (is_null($detail->rate_makan_fix)) {
                    $updateData['rate_makan_fix'] = \App\Models\SbuLembur::pickRate($sbuRates, 'Uang Makan Lembur', $golongan)?->besaran ?? 0;
                }

                $detail->update($updateData);
            }

            $overtime->update(['is_locked' => true]);
            return redirect()->back()->with('success', 'Data lembur bulan ini berhasil dikunci dan snapshot telah disimpan permanen.');
        }

        return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengunci data.');
    }

    public function unlock(Request $request, Package $package, $month)
    {
        $year = $package->created_at ? $package->created_at->format('Y') : date('Y');
        $overtime = Overtime::where('package_id', $package->id)
            ->where('bulan', $month)
            ->where('tahun', $year)
            ->firstOrFail();

        $userRole = auth()->user()->getRoleNames()->first() ?? '';
        if ($userRole === 'Admin') {
            $overtime->update(['is_locked' => false]);
            return redirect()->back()->with('success', 'Kunci data berhasil dibuka. Data dapat diedit kembali.');
        }

        return redirect()->back()->with('error', 'Hanya Admin yang dapat membuka kunci data.');
    }
}
