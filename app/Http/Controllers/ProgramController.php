<?php

namespace App\Http\Controllers;

use App\Models\Program;

class ProgramController extends Controller
{
    public function index()
    {
        $programs = Program::with('skpd')
            ->orderBy('tahun','desc')
            ->get();

        return view('programs.index', compact('programs'));
    }
}