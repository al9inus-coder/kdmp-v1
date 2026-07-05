<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\SbuLembur;
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
                'activities.subActivities.packages.procurementPackage.procurementProcess',
                'activities.subActivities.packages.procurementPackage.externalRecords',
                'activities.subActivities.packages.travelOrders.personnels',
                'activities.subActivities.packages.overtimes.details.employee',
            ])
            ->whereHas('activities.subActivities')
            ->orderBy('kode')
            ->get();

        $sbuRates = SbuLembur::all();

        return view('monev.index', compact('programs', 'sbuRates'));
    }

    public function show(SubActivity $subActivity): View
    {
        $subActivity->load([
            'activity.program',
            'packages' => function ($query) {
                $query->where('status', 'approved');
            },
            'packages.procurementPackage.procurementProcess',
            'packages.procurementPackage.externalRecords',
            'packages.travelOrders.personnels',
            'packages.overtimes.details.employee',
            'packages.account',
            'packages.fiscalYear',
        ]);

        $sbuRates = SbuLembur::all();

        return view('monev.show', compact('subActivity', 'sbuRates'));
    }

    public function print(SubActivity $subActivity): View
    {
        $subActivity->load([
            'activity.program',
            'packages' => function ($query) {
                $query->where('status', 'approved');
            },
            'packages.procurementPackage.procurementProcess',
            'packages.procurementPackage.externalRecords',
            'packages.travelOrders.personnels',
            'packages.overtimes.details.employee',
            'packages.account',
            'packages.fiscalYear'
        ]);

        return view('monev.print', compact('subActivity'));
    }
}
