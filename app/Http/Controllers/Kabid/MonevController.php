<?php

namespace App\Http\Controllers\Kabid;

use App\Http\Controllers\Controller;
use App\Models\BudgetLine;
use App\Models\FiscalYear;
use App\Models\Program;
use App\Models\SbuLembur;
use App\Models\SubActivity;
use Illuminate\View\View;

class MonevController extends Controller
{
    public function index(): View
    {
        // Hanya cabang DPA yang berjalan. Program/kegiatan/sub kegiatan yang
        // dinonaktifkan berarti tidak dilaksanakan tahun ini, jadi tidak
        // ditampilkan maupun ikut dijumlah.
        $programs = Program::aktif()
            ->with([
                'activities' => fn ($query) => $query->where('is_active', true)->orderBy('kode'),
                'activities.subActivities' => fn ($query) => $query->where('is_active', true)->orderBy('kode'),
                'activities.subActivities.packages' => function ($query) {
                    $query->where('status', 'approved');
                },
                'activities.subActivities.packages.procurementPackage.procurementProcess',
                'activities.subActivities.packages.procurementPackage.externalRecords',
                'activities.subActivities.packages.travelOrders.personnels',
                'activities.subActivities.packages.overtimes.details.employee',
            ])
            ->whereHas('activities', fn ($query) => $query
                ->where('is_active', true)
                ->whereHas('subActivities', fn ($sub) => $sub->where('is_active', true)))
            ->orderBy('kode')
            ->get();

        $sbuRates = SbuLembur::all();

        // Pagu monev bersumber dari plafon DPA; Sum pagu paket hanya dipakai
        // sebagai cadangan untuk sub kegiatan yang belum punya baris anggaran.
        $plafonSub = BudgetLine::plafonPerSubActivity($this->tahunAktif());

        return view('kabid.monev.index', compact('programs', 'sbuRates', 'plafonSub'));
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

        $tahun = $this->tahunAktif();
        $plafonSub = BudgetLine::plafonPerSubActivity($tahun);
        $barisAnggaran = BudgetLine::untukSubActivity($subActivity->id, $tahun);

        return view('kabid.monev.show', compact('subActivity', 'sbuRates', 'plafonSub', 'barisAnggaran', 'tahun'));
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
            'packages.fiscalYear',
        ]);

        $tahun = $this->tahunAktif();
        $plafonSub = BudgetLine::plafonPerSubActivity($tahun);
        $barisAnggaran = BudgetLine::untukSubActivity($subActivity->id, $tahun);

        return view('monev.print', compact('subActivity', 'plafonSub', 'barisAnggaran', 'tahun'));
    }

    /**
     * Tahun anggaran yang dipakai monev untuk mengambil plafon DPA.
     */
    private function tahunAktif(): ?int
    {
        return FiscalYear::where('is_active', true)->value('id')
            ?? FiscalYear::orderByDesc('tahun')->value('id');
    }
}
