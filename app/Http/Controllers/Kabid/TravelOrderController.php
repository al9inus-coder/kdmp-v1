<?php

namespace App\Http\Controllers\Kabid;

use App\Http\Controllers\TravelOrderController as BaseTravelOrderController;
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

class TravelOrderController extends BaseTravelOrderController
{
    // Prefix role untuk view & route (kabid / staf). Dioverride oleh Staff\TravelOrderController.
    protected string $rolePrefix = 'kabid';

    // Bila true, travel order mengikuti alur pengajuan (draf -> diajukan -> disetujui/revisi/tolak).
    protected bool $submissionFlow = false;

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

        return view($this->rolePrefix . '.travel-orders.create', compact(
            'package',
            'employees',
            'dalamDaerahDestinations',
            'luarDaerahKalbarDestinations',
            'luarDaerahLuarProvinsiDestinations'
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

        return view($this->rolePrefix . '.travel-orders.create', compact(
            'package',
            'travelOrder',
            'employees',
            'dalamDaerahDestinations',
            'luarDaerahKalbarDestinations',
            'luarDaerahLuarProvinsiDestinations'
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
        ], [
            'employees.required' => 'Pilih minimal satu pegawai pelaksana perjalanan dinas.',
            'employees.min' => 'Pilih minimal satu pegawai pelaksana perjalanan dinas.',
        ]);

        $submissionFlow = $this->submissionFlow;

        $travelOrder = DB::transaction(function () use ($package, $validated, $submissionFlow) {
            $extra = $submissionFlow
                ? ['status' => TravelOrder::STATUS_DRAFT, 'created_by' => Auth::id()]
                : [];

            $travelOrder = $package->travelOrders()->create(
                array_merge(Arr::except($validated, ['employees', 'kendaraan', 'kategori_tujuan']), $extra)
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
            ->route($this->rolePrefix . '.packages.travel-orders.show', [$package, $travelOrder])
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

        DB::transaction(function () use ($travelOrder, $validated) {
            $travelOrder->update(
                Arr::except($validated, ['employees', 'kendaraan', 'kategori_tujuan'])
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
            ->route($this->rolePrefix . '.packages.travel-orders.show', [$package, $travelOrder])
            ->with('success', 'Perjalanan dinas berhasil diperbarui.');
    }

    public function show(Package $package, TravelOrder $travelOrder): View
    {
        Gate::authorize('view', $package);

        abort_if((int) $travelOrder->package_id !== (int) $package->id, 404);

        $package->load('account', 'program', 'activity', 'subActivity', 'fiscalYear');
        $travelOrder->load('personnels.employee', 'creator', 'reviewer', 'spjReviewer');

        $days = Carbon::parse($travelOrder->tanggal_berangkat)
            ->diffInDays(Carbon::parse($travelOrder->tanggal_kembali)) + 1;

        $estimates = [];
        foreach ($travelOrder->personnels as $personnel) {
            $estimates[$personnel->id] = $this->calculateEstimatedCost(
                $personnel->employee,
                $travelOrder,
                $days,
                $personnel->jenis_kendaraan
            );
        }

        return view($this->rolePrefix . '.travel-orders.show', compact('package', 'travelOrder', 'estimates'));
    }

    public function submit(Package $package, TravelOrder $travelOrder)
    {
        Gate::authorize('view', $package);

        abort_if((int) $travelOrder->package_id !== (int) $package->id, 404);

        if (!in_array($travelOrder->status, [TravelOrder::STATUS_DRAFT, TravelOrder::STATUS_REVISION], true)) {
            return back()->with('error', 'Hanya SPPD berstatus Draf atau Perlu Revisi yang dapat diajukan.');
        }

        if ($travelOrder->personnels()->count() === 0) {
            return back()->with('error', 'Lengkapi peserta perjalanan dinas sebelum diajukan.');
        }

        $travelOrder->update([
            'status' => TravelOrder::STATUS_SUBMITTED,
            'submitted_at' => now(),
            'catatan_review' => null,
            'reviewed_at' => null,
            'reviewed_by' => null,
        ]);

        return redirect()
            ->route($this->rolePrefix . '.packages.travel-orders.show', [$package, $travelOrder])
            ->with('success', 'SPPD berhasil diajukan dan menunggu persetujuan Kabid.');
    }

    public function withdraw(Package $package, TravelOrder $travelOrder)
    {
        Gate::authorize('view', $package);

        abort_if((int) $travelOrder->package_id !== (int) $package->id, 404);

        if ($travelOrder->status !== TravelOrder::STATUS_SUBMITTED) {
            return back()->with('error', 'Hanya SPPD yang sedang Diajukan yang dapat ditarik kembali.');
        }

        $travelOrder->update([
            'status' => TravelOrder::STATUS_DRAFT,
            'submitted_at' => null,
        ]);

        return redirect()
            ->route($this->rolePrefix . '.packages.travel-orders.show', [$package, $travelOrder])
            ->with('success', 'Pengajuan SPPD ditarik kembali ke Draf.');
    }

    /**
     * Review SPPD oleh Kabid (hanya saat berstatus Diajukan).
     */
    public function approve(Package $package, TravelOrder $travelOrder)
    {
        return $this->reviewTransition($package, $travelOrder, TravelOrder::STATUS_APPROVED, 'SPPD disetujui.');
    }

    public function revise(Request $request, Package $package, TravelOrder $travelOrder)
    {
        $request->validate(['catatan_review' => 'required|string|max:2000'], [
            'catatan_review.required' => 'Catatan revisi wajib diisi.',
        ]);

        return $this->reviewTransition($package, $travelOrder, TravelOrder::STATUS_REVISION,
            'SPPD dikembalikan untuk direvisi.', $request->input('catatan_review'));
    }

    public function reject(Request $request, Package $package, TravelOrder $travelOrder)
    {
        $request->validate(['catatan_review' => 'required|string|max:2000'], [
            'catatan_review.required' => 'Alasan penolakan wajib diisi.',
        ]);

        return $this->reviewTransition($package, $travelOrder, TravelOrder::STATUS_REJECTED,
            'SPPD ditolak.', $request->input('catatan_review'));
    }

    /**
     * Review SPJ (biaya rampung) oleh Kabid — hanya Setujui / Revisi (tanpa tolak).
     */
    public function approveSpj(Package $package, TravelOrder $travelOrder)
    {
        return $this->spjReviewTransition($package, $travelOrder, TravelOrder::SPJ_APPROVED, 'SPJ SPD disetujui.');
    }

    public function reviseSpj(Request $request, Package $package, TravelOrder $travelOrder)
    {
        $request->validate(['spj_catatan' => 'required|string|max:2000'], [
            'spj_catatan.required' => 'Catatan revisi wajib diisi.',
        ]);

        return $this->spjReviewTransition($package, $travelOrder, TravelOrder::SPJ_REVISION,
            'SPJ dikembalikan untuk direvisi.', $request->input('spj_catatan'));
    }

    private function spjReviewTransition(Package $package, TravelOrder $travelOrder, string $status, string $message, ?string $catatan = null)
    {
        Gate::authorize('view', $package);

        abort_if((int) $travelOrder->package_id !== (int) $package->id, 404);

        if ($travelOrder->spjStatus() !== TravelOrder::SPJ_SUBMITTED) {
            return back()->with('error', 'Hanya SPJ yang sedang Diajukan yang dapat ditinjau.');
        }

        $travelOrder->update([
            'spj_status' => $status,
            'spj_catatan' => $catatan,
            'spj_reviewed_at' => now(),
            'spj_reviewed_by' => Auth::id(),
        ]);

        return redirect()
            ->route($this->rolePrefix . '.packages.travel-orders.show', [$package, $travelOrder])
            ->with('success', $message);
    }

    private function reviewTransition(Package $package, TravelOrder $travelOrder, string $status, string $message, ?string $catatan = null)
    {
        Gate::authorize('view', $package);

        abort_if((int) $travelOrder->package_id !== (int) $package->id, 404);

        if ($travelOrder->status !== TravelOrder::STATUS_SUBMITTED) {
            return back()->with('error', 'Hanya SPPD yang sedang Diajukan yang dapat ditinjau.');
        }

        $travelOrder->update([
            'status' => $status,
            'catatan_review' => $catatan,
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
        ]);

        return redirect()
            ->route($this->rolePrefix . '.packages.travel-orders.show', [$package, $travelOrder])
            ->with('success', $message);
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
