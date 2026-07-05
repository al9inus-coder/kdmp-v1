<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\Employee::query();
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
        }
        $employees = $query->orderBy('nama')->paginate(15);
        return view('employees.index', compact('employees'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('employees.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'nullable|string|max:50|unique:employees,nip',
            'golongan' => 'nullable|string|max:50',
            'jabatan' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'kategori_biaya' => [
                'nullable',
                'string',
                \Illuminate\Validation\Rule::in([
                    'Eselon II',
                    'Eselon III, Gol. IV dan Jafung Madya',
                    'Eselon IV, Gol. III kebawah, P3K, Jafung, Non ASN'
                ]),
            ],
        ]);

        \App\Models\Employee::create($validated);

        return redirect()->route('employees.index')->with('success', 'Pegawai berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(\App\Models\Employee $employee)
    {
        return view('employees.edit', compact('employee'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, \App\Models\Employee $employee)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'nullable|string|max:50|unique:employees,nip,' . $employee->id,
            'golongan' => 'nullable|string|max:50',
            'jabatan' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'kategori_biaya' => [
                'nullable',
                'string',
                \Illuminate\Validation\Rule::in([
                    'Eselon II',
                    'Eselon III, Gol. IV dan Jafung Madya',
                    'Eselon IV, Gol. III kebawah, P3K, Jafung, Non ASN'
                ]),
            ],
        ]);

        $employee->update($validated);

        return redirect()->route('employees.index')->with('success', 'Pegawai berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(\App\Models\Employee $employee)
    {
        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'Pegawai berhasil dihapus.');
    }
}
