<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\OvertimeController as BaseOvertimeController;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\Overtime;
use App\Models\Package;
use App\Models\SbuLembur;
use Illuminate\Support\Facades\Gate;

/**
 * Lembur untuk Staf — KHUSUS input kehadiran petugas kebersihan.
 * Mode dipilih kabid/admin; sebelum mode 'kebersihan' ditetapkan
 * staf hanya melihat layar tunggu. Aksi tulis (import, hapus via ajax)
 * diwarisi dari base controller dengan guard role di sana.
 */
class OvertimeController extends BaseOvertimeController
{
    /**
     * Daftar paket lembur yang dikelola staf sebagai pintu masuk menu sidebar
     * "Input Lembur" — route lembur butuh paket, jadi staf memilih paketnya
     * dari sini dulu. HANYA paket bermode 'kebersihan' yang tampil; paket
     * bermode dinas atau yang belum diatur kabid/admin bukan wilayah staf.
     * (Bukan index(): signature index di base controller berbeda.)
     */
    public function lemburIndex()
    {
        $packages = Package::query()
            ->where('jenis_pengadaan', 'like', '%wakelola%')
            ->whereHas('account', fn ($q) => $q->where('nama', 'like', '%lembur%'))
            ->whereHas('overtimes', fn ($q) => $q->where('jenis_lembur', 'kebersihan'))
            ->with(['account', 'subActivity', 'overtimes.details'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Package $package) {
                // Status tiap bulan utk strip 12 bulan di kartu:
                // 'terkunci' | 'terisi' | 'kosong'
                $months = [];
                for ($m = 1; $m <= 12; $m++) {
                    $ot = $package->overtimes->firstWhere('bulan', $m);
                    $adaJam = $ot && $ot->details
                        ->contains(fn ($d) => !empty(array_filter($d->daily_hours ?? [])));

                    $months[$m] = $ot && $ot->is_locked
                        ? 'terkunci'
                        : ($adaJam ? 'terisi' : 'kosong');
                }

                return [
                    'package' => $package,
                    'months' => $months,
                    'bulanTerisi' => count(array_filter($months, fn ($s) => $s !== 'kosong')),
                    'terkunci' => count(array_filter($months, fn ($s) => $s === 'terkunci')),
                    'lockedMonths' => array_keys(array_filter($months, fn ($s) => $s === 'terkunci')),
                ];
            });

        $dinasEmployees = Employee::where('tipe', Employee::TIPE_DINAS)->orderBy('nama')->get();

        return view('staf.lembur.index', compact('packages', 'dinasEmployees'));
    }

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
        $backUrl = route('staf.lembur.index');

        if ($mode !== 'kebersihan') {
            return view('overtimes.kebersihan-menunggu', compact('package', 'mode', 'backUrl'));
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
            'routePrefix' => 'staf',
            'canInput' => true,
            'canLock' => false,
            'canUnlock' => false,
            'canEditSbu' => false,
            'canChangeMode' => false,
            'dinasEmployees' => Employee::where('tipe', Employee::TIPE_DINAS)->orderBy('nama')->get(),
            'backUrl' => $backUrl,
        ]);
    }
}
