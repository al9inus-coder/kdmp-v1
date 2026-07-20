<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProcurementPackage;

class BukuRegisterController extends Controller
{
    public function index()
    {
        $packages = ProcurementPackage::whereHas('package', function ($query) {
            $query->where('metode_pengadaan', '!=', 'Dikecualikan')
                  ->where('jenis_pengadaan', '!=', 'Swakelola');
        })
        ->with([
            'package.program',
            'procurementRequest',
            'procurementProcess',
            'payment'
        ])
        ->whereNotNull('workflow_status')
        ->get();

        // Mengurutkan berdasarkan nomor surat permohonan secara natural
        $packages = $packages->sortBy(function ($item) {
            return $item->procurementRequest?->nomor_surat ?? '';
        }, SORT_NATURAL);

        // Mengelompokkan berdasarkan nama program
        $groupedPackages = $packages->groupBy(function ($item) {
            return $item->package && $item->package->program 
                ? $item->package->program->nama 
                : 'Tanpa Program';
        });

        // Mengurutkan grup sesuai urutan yang diminta: Persampahan di atas, Keanekaragaman Hayati di bawah
        $groupedPackages = $groupedPackages->sortBy(function ($packages, $programName) {
            $lowerName = strtolower($programName);
            if (strpos($lowerName, 'persampahan') !== false) {
                return 1;
            } elseif (strpos($lowerName, 'keanekaragaman hayati') !== false) {
                return 2;
            }
            return 3; // Lainnya di bawah
        });

        return view('buku-register.index', compact('groupedPackages'));
    }
}
