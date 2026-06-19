<?php

namespace App\Http\Controllers;

use App\Models\FiscalYear;
use App\Models\Package;
use App\Models\ProcurementPackage;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $fiscalYears = FiscalYear::orderBy('tahun', 'desc')->get();
        
        $fiscalYearId = $request->get('fiscal_year_id');
        if (!$fiscalYearId) {
            $activeYear = $fiscalYears->where('is_active', true)->first();
            $fiscalYearId = $activeYear ? $activeYear->id : ($fiscalYears->first()->id ?? null);
        }

        // Base Query
        $packagesQuery = Package::where('fiscal_year_id', $fiscalYearId);
        
        // Metrics
        $totalPagu = $packagesQuery->sum('pagu');
        $totalPackages = $packagesQuery->count();
        
        // Procurement Packages in this fiscal year
        $procurementPackagesQuery = ProcurementPackage::whereHas('package', function($q) use ($fiscalYearId) {
            $q->where('fiscal_year_id', $fiscalYearId);
        });

        $completedCount = (clone $procurementPackagesQuery)
            ->where('workflow_status', ProcurementPackage::WORKFLOW_COMPLETED)
            ->count();
            
        // Calculate realized budget (sum of nilai_kontrak for COMPLETED processes)
        $realizedBudget = \App\Models\ProcurementProcess::whereHas('procurementPackage', function($q) use ($fiscalYearId) {
            $q->where('workflow_status', ProcurementPackage::WORKFLOW_COMPLETED)
              ->whereHas('package', function($p) use ($fiscalYearId) {
                  $p->where('fiscal_year_id', $fiscalYearId);
              });
        })->sum('nilai_kontrak');
        
        $absorptionPercentage = $totalPagu > 0 ? round(($realizedBudget / $totalPagu) * 100, 2) : 0;

        // Status Distribution
        $statusDistribution = (clone $procurementPackagesQuery)
            ->selectRaw('workflow_status, count(*) as total')
            ->groupBy('workflow_status')
            ->pluck('total', 'workflow_status')
            ->toArray();
            
        // Jenis Pengadaan Distribution
        $jenisPengadaanDistribution = (clone $packagesQuery)
            ->selectRaw('jenis_pengadaan, count(*) as total')
            ->groupBy('jenis_pengadaan')
            ->pluck('total', 'jenis_pengadaan')
            ->toArray();

        // Late Packages (Warning System)
        // Find packages still in draft or preparation when current date > target date
        $latePackages = (clone $packagesQuery)
            ->where('target_procurement_date', '<', now())
            ->whereHas('procurementPackage', function($q) {
                $q->whereIn('workflow_status', [
                    ProcurementPackage::WORKFLOW_DRAFT,
                    ProcurementPackage::WORKFLOW_PREPARATION_COMPLETED
                ]);
            })
            ->with(['procurementPackage', 'activity', 'subActivity'])
            ->limit(10)
            ->get();
            
        // Recent Activities (latest updated procurement packages)
        $recentActivities = (clone $procurementPackagesQuery)
            ->with(['package'])
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'fiscalYears', 
            'fiscalYearId', 
            'totalPagu', 
            'totalPackages', 
            'completedCount',
            'realizedBudget',
            'absorptionPercentage',
            'statusDistribution',
            'jenisPengadaanDistribution',
            'latePackages',
            'recentActivities'
        ));
    }
}
