<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\ProcurementPackage;
use App\Models\PriceReference;
use App\Models\TechnicalSpecificationItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;


class PriceReferenceController extends Controller
{
    public function index(Package $package): View
    {
        $procurementPackage =
            $package->procurementPackage;

        abort_if(!$procurementPackage, 404);

        $procurementPackage->load([
            'package',
            'technicalSpecification.items',
            'priceReferences'
        ]);

        $priceReferences = $procurementPackage
            ->priceReferences
            ->sortBy('nama_barang_jasa');

        $groupedReferences =
            $priceReferences->groupBy('nama_barang_jasa');

        $technicalItems =
            $procurementPackage
                ->technicalSpecification
                ?->items
                ?? collect();

        return view(
            'price-references.index',
            compact(
                'procurementPackage',
                'groupedReferences',
                'technicalItems'
            )
        );
    }

public function create(Package $package): View|RedirectResponse
{
    $procurementPackage = $package->procurementPackage;

    $barangJasaOptions = $this->barangJasaOptions($procurementPackage);

    if ($barangJasaOptions === []) {
        return redirect()
            ->route('procurement-packages.price-references.index',
                $package
            )
            ->with(
                'warning',
                'Lengkapi Rincian Barang/Jasa pada Spesifikasi Teknis terlebih dahulu.'
            );
    }

        $defaultOption = reset($barangJasaOptions);

        $priceReference = new PriceReference([
            'nama_barang_jasa' => $defaultOption['nama_barang_jasa'],
            'volume' => $defaultOption['volume'],
            'satuan' => $defaultOption['satuan'],
            'harga_satuan' => 0,
        ]);

        return view('price-references.create', compact(
            'procurementPackage',
            'priceReference',
            'barangJasaOptions'
        ));
    }

    public function store(Request $request, Package $package): RedirectResponse
{
    $procurementPackage = $package->procurementPackage;

    $barangJasaOptions = $this->barangJasaOptions($procurementPackage);

        if ($barangJasaOptions === []) {
            return redirect()
                ->route('procurement-packages.price-references.index', $package)
                ->with('warning', 'Lengkapi Rincian Barang/Jasa pada Spesifikasi Teknis terlebih dahulu.');
        }

        $validated = $this->validateRequest($request, $barangJasaOptions);
        $selectedItemId = $validated['technical_specification_item_id'];
        unset($validated['technical_specification_item_id']);

        $barangJasa = $barangJasaOptions[$selectedItemId];

        $procurementPackage->priceReferences()->create(array_merge(
            $validated,
            [
                'nama_barang_jasa' => $barangJasa['nama_barang_jasa'],
                'volume' => $barangJasa['volume'],
                'satuan' => $barangJasa['satuan'],
                'jumlah_harga' => $this->calculateJumlahHarga($validated, $barangJasa),
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]
        ));

        return redirect()
            ->route('procurement-packages.price-references.index', $package)
            ->with('success', 'Referensi Harga berhasil ditambahkan.');
    }

    public function edit(
        Package $package,
        PriceReference $priceReference
    ): View|RedirectResponse {

        $procurementPackage = $package->procurementPackage;

        if ($priceReference->procurement_package_id !== $procurementPackage->id) {
            return redirect()->route('procurement-packages.price-references.index',
                $package
            );
        }

        $barangJasaOptions = $this->barangJasaOptions(
            $procurementPackage,
            $priceReference
        );

        return view(
            'price-references.edit',
            compact(
                'procurementPackage',
                'priceReference',
                'barangJasaOptions'
            )
        );
    }

    public function update(
    Request $request,
    Package $package,
    PriceReference $priceReference
): RedirectResponse {

    $procurementPackage = $package->procurementPackage;

    if ($priceReference->procurement_package_id !== $procurementPackage->id) {
        return redirect()->route('procurement-packages.price-references.index',
            $package
        );
    }

    $barangJasaOptions = $this->barangJasaOptions(
        $procurementPackage,
        $priceReference
    );

    $validated = $this->validateRequest(
    $request,
    $barangJasaOptions
    );

    $selectedItemId =
        $validated['technical_specification_item_id'];

    unset(
        $validated['technical_specification_item_id']
    );

    $barangJasa = $barangJasaOptions[$selectedItemId];

        $priceReference->update(array_merge(
            $validated,
            [
                'nama_barang_jasa' => $barangJasa['nama_barang_jasa'],
                'volume' => $barangJasa['volume'],
                'satuan' => $barangJasa['satuan'],
                'jumlah_harga' => $this->calculateJumlahHarga($validated, $barangJasa),
                'updated_by' => Auth::id(),
            ]
        ));

        return redirect()
            ->route('procurement-packages.price-references.index', $package)
            ->with('success', 'Referensi Harga berhasil diperbarui.');
    }

    public function destroy(
        Package $package,
        PriceReference $priceReference
    ): RedirectResponse {

        $procurementPackage = $package->procurementPackage;

        if ($priceReference->procurement_package_id !== $procurementPackage->id) {
            return redirect()->route('procurement-packages.price-references.index',
                $package
            );
        }

        $priceReference->delete();

        return redirect()
            ->route('procurement-packages.price-references.index',
                $package
            )
            ->with('success', 'Referensi Harga berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateRequest(Request $request, array $barangJasaOptions): array
    {
        return $request->validate([
            'technical_specification_item_id' => ['required', 'string', Rule::in(array_keys($barangJasaOptions))],
            'nama_produk_etalase' => ['nullable', 'string', 'max:255'],
            'nama_pelaku_usaha' => ['nullable', 'string', 'max:255'],
            'harga_satuan' => ['required', 'numeric', 'min:0'],
            'link_produk' => ['nullable', 'string'],
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function barangJasaOptions(
    ProcurementPackage $procurementPackage,
    ?PriceReference $currentPriceReference = null
): array {
        $options = [];

        $procurementPackage->loadMissing('technicalSpecification.items');

        ($procurementPackage->technicalSpecification?->items ?? collect())
            ->each(function (TechnicalSpecificationItem $item) use (&$options): void {
                $options[(string) $item->id] = [
                    'id' => (string) $item->id,
                    'nama_barang_jasa' => $item->nama_barang_jasa,
                    'volume' => (float) $item->volume,
                    'satuan' => $item->satuan,
                ];
            });

        if ($currentPriceReference) {
            $matchedOption = collect($options)
                ->first(fn (array $option) => $option['nama_barang_jasa'] === $currentPriceReference->nama_barang_jasa);

            if (!$matchedOption) {
                $options['current-'.$currentPriceReference->id] = [
                    'id' => 'current-'.$currentPriceReference->id,
                    'nama_barang_jasa' => $currentPriceReference->nama_barang_jasa,
                    'volume' => (float) $currentPriceReference->volume,
                    'satuan' => $currentPriceReference->satuan,
                ];
            }
        }

        return $options;
    }

    /**
     * @param array<string, mixed> $validated
     */
    private function calculateJumlahHarga(array $validated, array $barangJasa): float
    {
        return (float) $barangJasa['volume'] * (float) $validated['harga_satuan'];
    }

    public function print(Package $package)
    {
        $procurementPackage = $package->procurementPackage;

        abort_if(!$procurementPackage, 404);

        $procurementPackage->load([
            'package',
            'technicalSpecification.items',
            'priceReferences'
        ]);

        $technicalItems = $procurementPackage->technicalSpecification?->items ?? collect();
        $priceReferences = $procurementPackage->priceReferences ?? collect();
        $groupedReferences = $priceReferences->groupBy('nama_barang_jasa');

        return view('price-references.pdf', compact('procurementPackage', 'technicalItems', 'groupedReferences'));
    }
}
