<?php

namespace App\Http\Controllers;

use App\Models\FiscalYear;
use Illuminate\Http\Request;

class FiscalYearController extends Controller
{
    public function index()
    {
        $years = FiscalYear::orderBy('tahun','desc')->get();

        return view('fiscal_years.index', compact('years'));
    }

    public function create()
    {
        return view('fiscal_years.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tahun' => 'required|unique:fiscal_years'
        ]);

        FiscalYear::create([
            'tahun' => $request->tahun,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()
            ->route('admin.fiscal-years.index')
            ->with('success','Tahun anggaran berhasil ditambahkan');
    }

    public function activate(FiscalYear $fiscalYear)
    {
        FiscalYear::query()
            ->update([
                'is_active' => false
            ]);

        $fiscalYear->update([
            'is_active' => true
        ]);

        return back()
            ->with('success','Tahun anggaran aktif berhasil diubah');
    }
}
