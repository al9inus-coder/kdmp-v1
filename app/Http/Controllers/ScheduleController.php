<?php

namespace App\Http\Controllers;

use App\Models\FiscalYear;
use App\Models\Package;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $fiscalYears = FiscalYear::orderBy('tahun', 'desc')->get();
        
        $fiscalYearId = $request->get('fiscal_year_id');
        if (!$fiscalYearId) {
            $activeYear = $fiscalYears->where('is_active', true)->first();
            $fiscalYearId = $activeYear ? $activeYear->id : ($fiscalYears->first()->id ?? null);
        }

        // Gantt Chart Data (Packages with Schedule)
        // Here we can get all packages or top 100 to avoid freezing
        $packages = Package::where('fiscal_year_id', $fiscalYearId)
            ->whereNotNull('pemilihan_mulai_bulan')
            ->whereNotNull('pemilihan_selesai_bulan')
            ->whereNotNull('kontrak_mulai_bulan')
            ->whereNotNull('kontrak_selesai_bulan')
            ->orderBy('pagu', 'desc')
            ->limit(50)
            ->get();

        return view('schedules.index', compact('fiscalYears', 'fiscalYearId', 'packages'));
    }
}
