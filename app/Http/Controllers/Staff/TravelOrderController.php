<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Kabid\TravelOrderController as KabidTravelOrderController;
use App\Models\Employee;
use App\Models\Package;
use App\Models\SbuTransportRate;
use App\Models\SbuUangHarian;
use App\Models\TravelOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Modul Perjalanan Dinas untuk Staff.
 * Staff yang membuat & mengubah SPPD; Kabid hanya meninjau (tidak boleh create/edit).
 */
class TravelOrderController extends KabidTravelOrderController
{
    protected string $rolePrefix = 'staf';

    protected bool $submissionFlow = true;

    public function create(Package $package): View
    {
        Gate::authorize('view', $package);

        $package->load('account', 'program', 'activity', 'subActivity', 'fiscalYear');

        [
            'employees' => $employees,
            'dalamDaerahDestinations' => $dalamDaerahDestinations,
            'luarDaerahKalbarDestinations' => $luarDaerahKalbarDestinations,
            'luarDaerahLuarProvinsiDestinations' => $luarDaerahLuarProvinsiDestinations,
        ] = $this->travelOrderFormData();

        $eligiblePackages = Package::with(['subActivity', 'account'])
            ->where('status', 'approved')
            ->whereHas('account', fn ($q) => $q->where('nama', 'like', '%perjalanan dinas%'))
            ->get();

        $jadwalTerpakai = $this->jadwalTerpakai();
        $defaultDate = request()->query('date');           // tanggal berangkat
        $defaultEndDate = request()->query('end');          // tanggal kembali (opsional, dari drag di kalender)

        return view($this->rolePrefix . '.travel-orders.create', compact(
            'package',
            'employees',
            'dalamDaerahDestinations',
            'luarDaerahKalbarDestinations',
            'luarDaerahLuarProvinsiDestinations',
            'eligiblePackages',
            'jadwalTerpakai',
            'defaultDate',
            'defaultEndDate'
        ));
    }

    public function edit(Package $package, TravelOrder $travelOrder): View|\Illuminate\Http\RedirectResponse
    {
        Gate::authorize('view', $package);

        abort_if((int) $travelOrder->package_id !== (int) $package->id, 404);

        // Dalam alur pengajuan, hanya draf/revisi yang boleh diubah.
        if ($this->submissionFlow && !$travelOrder->isEditableBySubmitter()) {
            return redirect()
                ->route($this->rolePrefix . '.packages.travel-orders.show', [$package, $travelOrder])
                ->with('error', 'SPPD yang sudah diajukan tidak dapat diubah.');
        }

        $package->load('account', 'program', 'activity', 'subActivity', 'fiscalYear');
        $travelOrder->load('personnels.employee');

        [
            'employees' => $employees,
            'dalamDaerahDestinations' => $dalamDaerahDestinations,
            'luarDaerahKalbarDestinations' => $luarDaerahKalbarDestinations,
            'luarDaerahLuarProvinsiDestinations' => $luarDaerahLuarProvinsiDestinations,
        ] = $this->travelOrderFormData();

        $eligiblePackages = Package::with(['subActivity', 'account'])
            ->where('status', 'approved')
            ->whereHas('account', fn ($q) => $q->where('nama', 'like', '%perjalanan dinas%'))
            ->get();

        $jadwalTerpakai = $this->jadwalTerpakai($travelOrder->id);

        return view($this->rolePrefix . '.travel-orders.create', compact(
            'package',
            'travelOrder',
            'employees',
            'dalamDaerahDestinations',
            'luarDaerahKalbarDestinations',
            'luarDaerahLuarProvinsiDestinations',
            'eligiblePackages',
            'jadwalTerpakai'
        ));
    }

    public function store(Request $request, Package $package)
    {
        Gate::authorize('view', $package);

        $validated = $request->validate([
            'tipe_perjalanan' => 'required|in:Dalam Daerah,Luar Daerah',
            'kategori_tujuan' => 'nullable|in:Dalam Provinsi,Luar Provinsi',
            'dasar_pelaksanaan' => 'nullable|string',
            'maksud_perjalanan' => 'required|string',
            'tempat_tujuan' => 'required|string',
            'tanggal_berangkat' => 'required|date',
            'tanggal_kembali' => 'required|date|after_or_equal:tanggal_berangkat',
            'tanggal_surat' => 'nullable|date',
            'employees' => 'required|array|min:1',
            'employees.*' => 'exists:employees,id',
            'kendaraan' => 'nullable|array',
            'kendaraan.*' => 'in:mobil,motor,pesawat,pengikut',
            'package_id' => 'nullable|exists:packages,id',
        ], [
            'employees.required' => 'Pilih minimal satu pegawai pelaksana perjalanan dinas.',
            'employees.min' => 'Pilih minimal satu pegawai pelaksana perjalanan dinas.',
        ]);

        // Satu pegawai tidak boleh punya dua perjalanan dinas di tanggal yang beririsan.
        $bentrok = TravelOrder::bentrokJadwal(
            array_map('intval', $validated['employees']),
            $validated['tanggal_berangkat'],
            $validated['tanggal_kembali'],
        );

        if ($bentrok) {
            return back()->withInput()->withErrors(['employees' => TravelOrder::pesanBentrok($bentrok)]);
        }

        $submissionFlow = $this->submissionFlow;

        $newPackageId = $request->input('package_id', $package->id);
        $targetPackage = $newPackageId != $package->id ? \App\Models\Package::findOrFail($newPackageId) : $package;

        $travelOrder = DB::transaction(function () use ($targetPackage, $validated, $submissionFlow) {
            $extra = $submissionFlow
                ? ['status' => TravelOrder::STATUS_DRAFT, 'created_by' => Auth::id()]
                : [];

            $travelOrder = $targetPackage->travelOrders()->create(
                array_merge(Arr::except($validated, ['employees', 'kendaraan', 'kategori_tujuan', 'package_id']), $extra)
            );

            $days = Carbon::parse($travelOrder->tanggal_berangkat)
                ->diffInDays(Carbon::parse($travelOrder->tanggal_kembali)) + 1;

            foreach ($validated['employees'] as $index => $employeeId) {
                $employee = Employee::findOrFail($employeeId);
                $jenisKendaraan = $validated['kendaraan'][$employeeId] ?? 'mobil';
                $estimates = $this->calculateEstimatedCost($employee, $travelOrder, $days, $jenisKendaraan);

                $travelOrder->personnels()->create([
                    'employee_id' => $employeeId,
                    'urutan' => $index,
                    'jenis_kendaraan' => $jenisKendaraan,
                    'uang_harian' => $estimates['uang_harian'],
                    'biaya_penginapan' => $estimates['biaya_penginapan'],
                    'biaya_representasi' => $estimates['biaya_representasi'],
                    'biaya_transport' => $estimates['biaya_transport'],
                    'biaya_taksi' => $estimates['biaya_taksi'] ?? 0,
                ]);
            }

            return $travelOrder;
        });

        return redirect()
            ->route($this->rolePrefix . '.packages.travel-orders.show', [$targetPackage, $travelOrder])
            ->with('success', 'Perjalanan dinas berhasil ditambahkan.');
    }

    public function update(Request $request, Package $package, TravelOrder $travelOrder)
    {
        Gate::authorize('view', $package);

        abort_if((int) $travelOrder->package_id !== (int) $package->id, 404);

        if ($this->submissionFlow && !$travelOrder->isEditableBySubmitter()) {
            return redirect()
                ->route($this->rolePrefix . '.packages.travel-orders.show', [$package, $travelOrder])
                ->with('error', 'SPPD yang sudah diajukan tidak dapat diubah.');
        }

        $validated = $this->validateTravelOrder($request);

        $request->validate([
            'package_id' => 'nullable|exists:packages,id'
        ]);

        // Bentrok jadwal — SPPD yang sedang diedit dikecualikan dari pengecekan.
        $bentrok = TravelOrder::bentrokJadwal(
            array_map('intval', $validated['employees']),
            $validated['tanggal_berangkat'],
            $validated['tanggal_kembali'],
            $travelOrder->id,
        );

        if ($bentrok) {
            return back()->withInput()->withErrors(['employees' => TravelOrder::pesanBentrok($bentrok)]);
        }

        $newPackageId = $request->input('package_id', $package->id);
        $packageChanged = (int) $newPackageId !== (int) $package->id;
        $newPackage = $packageChanged ? Package::findOrFail($newPackageId) : $package;

        DB::transaction(function () use ($travelOrder, $validated, $newPackageId) {
            $travelOrder->update(
                array_merge(
                    Arr::except($validated, ['employees', 'kendaraan', 'kategori_tujuan']),
                    ['package_id' => $newPackageId]
                )
            );

            $days = Carbon::parse($travelOrder->tanggal_berangkat)
                ->diffInDays(Carbon::parse($travelOrder->tanggal_kembali)) + 1;

            $selectedEmployeeIds = collect($validated['employees'])->map(fn ($id) => (int) $id)->all();

            $travelOrder->personnels()
                ->whereNotIn('employee_id', $selectedEmployeeIds)
                ->delete();

            foreach ($selectedEmployeeIds as $index => $employeeId) {
                $employee = Employee::findOrFail($employeeId);
                $jenisKendaraan = $validated['kendaraan'][$employeeId] ?? 'mobil';
                $estimates = $this->calculateEstimatedCost($employee, $travelOrder, $days, $jenisKendaraan);

                $travelOrder->personnels()->updateOrCreate(
                    ['employee_id' => $employeeId],
                    [
                        'urutan' => $index,
                        'jenis_kendaraan' => $jenisKendaraan,
                        'uang_harian' => $estimates['uang_harian'],
                        'biaya_penginapan' => $estimates['biaya_penginapan'],
                        'biaya_representasi' => $estimates['biaya_representasi'],
                        'biaya_transport' => $estimates['biaya_transport'],
                        'biaya_taksi' => $estimates['biaya_taksi'] ?? 0,
                    ]
                );
            }
        });

        return redirect()
            ->route($this->rolePrefix . '.packages.travel-orders.show', [$newPackage, $travelOrder])
            ->with('success', 'Perjalanan dinas berhasil diperbarui.');
    }

    /**
     * Jadwal perjalanan dinas yang sudah terpakai per pegawai (untuk peringatan bentrok di form).
     *
     * @return array<int, array<int, array{start:string,end:string,tujuan:string}>>
     */
    protected function jadwalTerpakai(?int $exceptTravelOrderId = null): array
    {
        return \App\Models\TravelPersonnel::query()
            ->whereHas('travelOrder', fn ($q) => $q
                ->where('status', '!=', TravelOrder::STATUS_REJECTED)
                ->whereDate('tanggal_kembali', '>=', now()->subMonths(3))
                ->when($exceptTravelOrderId, fn ($sub) => $sub->where('id', '!=', $exceptTravelOrderId)))
            ->with('travelOrder:id,tempat_tujuan,tanggal_berangkat,tanggal_kembali')
            ->get()
            ->groupBy('employee_id')
            ->map(fn ($rows) => $rows->map(fn ($p) => [
                'start' => $p->travelOrder->tanggal_berangkat->format('Y-m-d'),
                'end' => $p->travelOrder->tanggal_kembali->format('Y-m-d'),
                'tujuan' => $p->travelOrder->tempat_tujuan,
            ])->values()->all())
            ->all();
    }

    protected function travelOrderFormData(): array
    {
        return [
            'employees' => Employee::orderBy('nama')->get(),
            'dalamDaerahDestinations' => SbuTransportRate::where('kategori', 'dalam_daerah')
                ->select('tempat_tujuan')
                ->distinct()
                ->orderBy('tempat_tujuan')
                ->pluck('tempat_tujuan'),
            'luarDaerahKalbarDestinations' => SbuTransportRate::where('kategori', 'luar_daerah')
                ->orderBy('tempat_tujuan')
                ->pluck('tempat_tujuan'),
            'luarDaerahLuarProvinsiDestinations' => SbuUangHarian::select('provinsi')
                ->where('provinsi', '!=', 'Kalimantan Barat')
                ->distinct()
                ->orderBy('provinsi')
                ->pluck('provinsi'),
        ];
    }

    protected function validateTravelOrder(Request $request): array
    {
        return $request->validate([
            'tipe_perjalanan' => 'required|in:Dalam Daerah,Luar Daerah',
            'kategori_tujuan' => 'nullable|in:Dalam Provinsi,Luar Provinsi',
            'dasar_pelaksanaan' => 'nullable|string',
            'maksud_perjalanan' => 'required|string',
            'tempat_tujuan' => 'required|string',
            'tanggal_berangkat' => 'required|date',
            'tanggal_kembali' => 'required|date|after_or_equal:tanggal_berangkat',
            'tanggal_surat' => 'nullable|date',
            'employees' => 'required|array|min:1',
            'employees.*' => 'exists:employees,id',
            'kendaraan' => 'nullable|array',
            'kendaraan.*' => 'in:mobil,motor,pesawat,pengikut',
        ], [
            'employees.required' => 'Pilih minimal satu pegawai pelaksana perjalanan dinas.',
            'employees.min' => 'Pilih minimal satu pegawai pelaksana perjalanan dinas.',
        ]);
    }
}
