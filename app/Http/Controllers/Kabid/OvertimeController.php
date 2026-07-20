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

        $mode = $this->resolveMode($package, $overtime);

        $sbuRates = SbuLembur::all();

        $holidaysDataFull = [];
        foreach (Holiday::whereYear('holiday_date', $year)->get() as $h) {
            $holidaysDataFull[] = [
                'date' => $h->holiday_date,
                'description' => $h->description,
            ];
        }

        $overtime->load('details.employee');

        // Mode kebersihan memakai tampilan khusus: input oleh staf/admin,
        // kabid hanya melihat + mengunci.
        if ($mode === 'kebersihan') {
            $userRole = auth()->user()->getRoleNames()->first() ?? '';
            $isAdmin = in_array($userRole, ['Admin', 'Super Admin']);
            $routePrefix = $isAdmin ? 'admin' : 'kabid';

            $lockedMonths = Overtime::where('package_id', $package->id)
                ->where('tahun', $year)
                ->where('is_locked', true)
                ->pluck('bulan')->map(fn ($b) => (int) $b)->all();

            return view('overtimes.kebersihan', [
                'lockedMonths' => $lockedMonths,
                'package' => $package,
                'overtime' => $overtime,
                'month' => (int) $month,
                'year' => (int) $year,
                'sbuRates' => $sbuRates,
                'holidaysDataFull' => $holidaysDataFull,
                'routePrefix' => $routePrefix,
                'canInput' => $isAdmin,
                'canLock' => true,
                'canUnlock' => $isAdmin,
                'canEditSbu' => $isAdmin,
                'canChangeMode' => true,
                'dinasEmployees' => Employee::where('tipe', Employee::TIPE_DINAS)->orderBy('nama')->get(),
                'backUrl' => route($routePrefix . '.procurement-packages.show', $package),
            ]);
        }

        return view('kabid.overtimes.show', compact(
            'package', 'overtime', 'month', 'year', 'sbuRates', 'holidaysDataFull', 'mode'
        ));
    }
}
