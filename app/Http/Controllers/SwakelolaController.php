<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;

class SwakelolaController extends Controller
{
    /**
     * Display a listing of the swakelola packages.
     */
    public function index(Request $request)
    {
        $query = Package::with('subActivity')
            ->where('jenis_pengadaan', 'Swakelola')
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_paket', 'like', "%{$search}%")
                  ->orWhere('id_rup', 'like', "%{$search}%");
            });
        }

        $packages = $query->paginate(15);

        return view('swakelola.index', compact('packages'));
    }
}
