<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\ProcurementPackage;
use App\Models\ProcurementRequest;
use App\Models\Skpd;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProcurementRequestController extends Controller
{
    public function create(Package $package): View|RedirectResponse
    {
        $procurementPackage =
            $package->procurementPackage;

        abort_if(!$procurementPackage, 404);
        if ($procurementPackage->procurementRequest) {

            return redirect()->route(
                'procurement-packages.procurement-request.show',
                $package
            );
        }

        $vendors = $procurementPackage
        ->priceReferences()
        ->whereNotNull('nama_pelaku_usaha')
        ->where('nama_pelaku_usaha', '!=', '')
        ->select('nama_pelaku_usaha')
        ->distinct()
        ->orderBy('nama_pelaku_usaha')
        ->pluck('nama_pelaku_usaha');
        
        return view(
            'procurement-requests.create',
            [
                'procurementPackage' => $procurementPackage,
                'procurementRequest' => new ProcurementRequest(),
                'vendors' => $vendors,
            ]
        );
    }

    public function store(
        Request $request,
        Package $package
    ): RedirectResponse
    {
        $procurementPackage =
            $package->procurementPackage;

        abort_if(!$procurementPackage, 404);

        if ($procurementPackage->procurementRequest) {

            return redirect()->route(
                'procurement-packages.procurement-request.show',
                $package
            )->with(
                'warning',
                'Surat Permohonan sudah tersedia.'
            );

        }

        $validated =
            $this->validateRequest($request);

        $procurementPackage
            ->procurementRequest()
            ->create(
                array_merge(
                    $validated,
                    [
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                    ]
                )
            );

        return redirect()
            ->route(
                'procurement-packages.procurement-request.show',
                $package
            )
            ->with(
                'success',
                'Surat Permohonan berhasil dibuat.'
            );
    }

    public function show(
    Package $package
): View|RedirectResponse
{
    $procurementPackage =
        $package->procurementPackage;

    abort_if(!$procurementPackage, 404);

    $procurementRequest =
        $procurementPackage->procurementRequest;

    if (!$procurementRequest) {

        return redirect()
            ->route(
                'procurement-packages.procurement-request.create',
                $package
            )
            ->with(
                'warning',
                'Surat Permohonan belum dibuat.'
            );

    }
    $nomorSuratLengkap =
        '000.3.2/'
        .$procurementRequest->nomor_surat
        .'/SP-PBJ/'
        .$procurementPackage->package->program->kode
        .'/PERKIMPLH-C';

    $procurementPackage->load([
        'package.program',
        'package.activity',
        'package.subActivity',
        'package.fiscalYear',
    ]);
    $skpd = Skpd::first();
    return view(
        'procurement-requests.show',
        compact(
            'procurementPackage',
            'procurementRequest',
            'nomorSuratLengkap',
            'skpd'
        )
    );
}

    public function edit(Package $package): View|RedirectResponse
    {
        $procurementPackage =
            $package->procurementPackage;

        abort_if(!$procurementPackage, 404);

        $procurementRequest =
            $procurementPackage->procurementRequest;

        if (!$procurementRequest) {

            return redirect()->route(
                'procurement-packages.procurement-request.create',
                $package
            );

        }

        $vendors = $procurementPackage
        ->priceReferences()
        ->whereNotNull('nama_pelaku_usaha')
        ->where('nama_pelaku_usaha', '!=', '')
        ->select('nama_pelaku_usaha')
        ->distinct()
        ->orderBy('nama_pelaku_usaha')
        ->pluck('nama_pelaku_usaha');
        return view(
            'procurement-requests.edit',
            compact(
                'procurementPackage',
                'procurementRequest',
                'vendors'
            )
        );
    }

    public function update(
        Request $request,
        Package $package
    ): RedirectResponse
    {
        $procurementPackage =
            $package->procurementPackage;

        abort_if(!$procurementPackage, 404);

        $procurementRequest =
            $procurementPackage->procurementRequest;

        if (!$procurementRequest) {

            return redirect()->route(
                'procurement-packages.procurement-request.create',
                $package
            );

        }

        $validated =
            $this->validateRequest($request);

        $procurementRequest->update(
            array_merge(
                $validated,
                [
                    'updated_by' => Auth::id(),
                ]
            )
        );

        return redirect()
            ->route(
                'procurement-packages.procurement-request.show',
                $package
            )
            ->with(
                'success',
                'Surat Permohonan berhasil diperbarui.'
            );
    }
    /**
     * @return array<string, mixed>
     */
    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'nomor_surat' => ['nullable', 'string', 'max:255'],
            'tanggal_surat' => ['nullable', 'date'],
            'nama_pejabat_pengadaan' => ['nullable','string','max:255'],
            'nama_penyedia' => ['nullable', 'string', 'max:255'],
            'alasan_pemilihan_penyedia' => ['nullable','string'],
        ]);
    }

    public function print(Package $package): View
    {
        $procurementPackage =
            $package->procurementPackage;

        abort_if(!$procurementPackage, 404);

        $procurementRequest =
            $procurementPackage->procurementRequest;

        abort_if(!$procurementRequest, 404);

        $procurementPackage->load([
            'package.program',
            'package.activity',
            'package.subActivity',
            'package.fiscalYear',
            'technicalSpecification.items',
        ]);

        $nomorSuratLengkap =
        '000.3.2/'
        .$procurementRequest->nomor_surat
        .'/SP-PBJ/'
        .$procurementPackage->package->program->kode
        .'/PERKIMPLH-C';
        $skpd = Skpd::first();
        return view(
            'procurement-requests.print',
            compact(
                'procurementPackage',
                'procurementRequest',
                'nomorSuratLengkap',
                'skpd'
            )
        );
    }
}
