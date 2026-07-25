<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\BudgetLine;
use App\Models\FiscalYear;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ControlCardController extends Controller
{
    public function print(Activity $activity): View
    {
        $activity->load([
            'program',
            // Sub kegiatan non-aktif tidak dijalankan di DPA, jadi tidak
            // dicetak. Paket disaring 'approved' seperti monev supaya angka
            // kedua kartu kendali tidak berbeda.
            'subActivities' => fn ($query) => $query->where('is_active', true)->orderBy('kode'),
            'subActivities.packages' => fn ($query) => $query->where('status', 'approved'),
            'subActivities.packages.account',
            'subActivities.packages.fiscalYear',
            'subActivities.packages.procurementPackage.procurementProcess',
            'subActivities.packages.procurementPackage.externalRecords',
            'subActivities.packages.travelOrders.personnels',
            'subActivities.packages.overtimes.details.employee',
        ]);

        // Pagu kartu kendali bersumber dari plafon DPA; jumlah pagu paket hanya
        // dipakai sebagai cadangan bila rekeningnya belum punya baris anggaran.
        $tahun = FiscalYear::where('is_active', true)->value('id')
            ?? FiscalYear::orderByDesc('tahun')->value('id');

        $barisAnggaran = BudgetLine::untukSubActivity(
            $activity->subActivities->pluck('id')->all(),
            $tahun
        )->groupBy('sub_activity_id');

        return view('control_cards.print', compact('activity', 'barisAnggaran', 'tahun'));
    }
}
