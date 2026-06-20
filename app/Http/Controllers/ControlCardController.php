<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ControlCardController extends Controller
{
    public function print(Activity $activity): View
    {
        $activity->load([
            'program',
            'subActivities.packages.account',
            'subActivities.packages.fiscalYear',
            'subActivities.packages.procurementPackage.procurementProcess'
        ]);

        return view('control_cards.print', compact('activity'));
    }
}
