<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\SubActivity;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MonevController extends Controller
{
    public function index(Request $request): View
    {
        $programs = Program::query()
            ->with([
                'activities.subActivities.packages' => function ($query) {
                    $query->where('status', 'approved');
                },
                'activities.subActivities.packages.procurementPackage.procurementProcess'
            ])
            ->whereHas('activities.subActivities')
            ->orderBy('kode')
            ->get();

        return view('monev.index', compact('programs'));
    }

    public function show(SubActivity $subActivity): View
    {
        $subActivity->load([
            'activity.program',
            'packages' => function ($query) {
                $query->where('status', 'approved');
            },
            'packages.procurementPackage.procurementProcess',
            'packages.account',
            'packages.fiscalYear'
        ]);

        return view('monev.show', compact('subActivity'));
    }

    public function print(SubActivity $subActivity): View
    {
        $subActivity->load([
            'activity.program',
            'packages' => function ($query) {
                $query->where('status', 'approved');
            },
            'packages.procurementPackage.procurementProcess',
            'packages.account',
            'packages.fiscalYear'
        ]);

        return view('monev.print', compact('subActivity'));
    }
}
