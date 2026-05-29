<?php

namespace App\Http\Controllers;

use App\Models\Skpd;
use Illuminate\Http\Request;

class SkpdController extends Controller
{
    public function index()
    {
        $skpds = Skpd::latest()->get();

        return view('skpds.index', compact('skpds'));
    }

    public function create()
    {
        return view('skpds.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode' => 'required|unique:skpds',
            'nama' => 'required',
        ]);

        Skpd::create($request->all());

        return redirect()
            ->route('skpds.index')
            ->with('success', 'SKPD berhasil ditambahkan');
    }
}