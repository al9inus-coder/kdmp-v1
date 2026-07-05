<?php

namespace App\Http\Controllers\Kabid;

use App\Http\Controllers\OvertimeController as BaseOvertimeController;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\Overtime;
use App\Models\OvertimeDetail;
use App\Models\Package;
use App\Models\SbuLembur;
use Illuminate\Support\Facades\Gate;

/**
 * Overtime (lembur) untuk Kabid. Logika aksi (updateAjax, autoFill, lock, dst.)
 * diwarisi dari base controller — semuanya mengembalikan JSON / back(), jadi cocok
 * untuk route Kabid. Hanya show() yang dioverride agar memakai view KDMP.
 */
class OvertimeController extends BaseOvertimeController
{
    public function show(Package $package, $month)
    {
        Gate::authorize('view', $package);

        $year = $package->created_at ? $package->created_at->format('Y') : date('Y');

        $overtime = Overtime::firstOrCreate([
            'package_id' => $package->id,
            'bulan' => $month,
            'tahun' => $year,
        ]);

        if ($overtime->details()->count() == 0) {
            foreach (Employee::all() as $employee) {
                OvertimeDetail::create([
                    'overtime_id' => $overtime->id,
                    'employee_id' => $employee->id,
                    'daily_hours' => [],
                    'use_uang_makan' => false,
                ]);
            }
        }

        $sbuRates = SbuLembur::all();

        $holidaysDataFull = [];
        foreach (Holiday::whereYear('holiday_date', $year)->get() as $h) {
            $holidaysDataFull[] = [
                'date' => $h->holiday_date,
                'description' => $h->description,
            ];
        }

        $overtime->load('details.employee');

        return view('kabid.overtimes.show', compact(
            'package', 'overtime', 'month', 'year', 'sbuRates', 'holidaysDataFull'
        ));
    }
}
