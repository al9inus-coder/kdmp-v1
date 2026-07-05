<?php

namespace App\Http\Controllers\Kabid;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PriceReference;
use App\Models\ProcurementPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class PriceReferenceController extends Controller
{
    public function store(Request $request, Package $package): RedirectResponse
    {
        Gate::authorize('view', $package);

        $procurementPackage = $package->procurementPackage;

        $this->assertEditable($procurementPackage);

        [$validated, $item] = $this->validateWithItem($request, $procurementPackage);

        $procurementPackage->priceReferences()->create(array_merge($validated, [
            'nama_barang_jasa' => $item->nama_barang_jasa,
            'volume' => (float) $item->volume,
            'satuan' => $item->satuan,
            'jumlah_harga' => (float) $item->volume * (float) $validated['harga_satuan'],
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]));

        return $this->backToPanel($package, 'Referensi harga berhasil ditambahkan.');
    }

    public function update(Request $request, Package $package, PriceReference $priceReference): RedirectResponse
    {
        Gate::authorize('view', $package);

        $procurementPackage = $package->procurementPackage;

        $this->assertEditable($procurementPackage);
        abort_if($priceReference->procurement_package_id !== $procurementPackage->id, 404);

        [$validated, $item] = $this->validateWithItem($request, $procurementPackage);

        $priceReference->update(array_merge($validated, [
            'nama_barang_jasa' => $item->nama_barang_jasa,
            'volume' => (float) $item->volume,
            'satuan' => $item->satuan,
            'jumlah_harga' => (float) $item->volume * (float) $validated['harga_satuan'],
            'updated_by' => Auth::id(),
        ]));

        return $this->backToPanel($package, 'Referensi harga berhasil diperbarui.');
    }

    public function destroy(Package $package, PriceReference $priceReference): RedirectResponse
    {
        Gate::authorize('view', $package);

        $procurementPackage = $package->procurementPackage;

        $this->assertEditable($procurementPackage);
        abort_if($priceReference->procurement_package_id !== $procurementPackage->id, 404);

        $priceReference->delete();

        return $this->backToPanel($package, 'Referensi harga berhasil dihapus.');
    }

    private function assertEditable(?ProcurementPackage $procurementPackage): void
    {
        abort_if(!$procurementPackage, 404);

        abort_if(
            $procurementPackage->workflow_status !== ProcurementPackage::WORKFLOW_DRAFT,
            403,
            'Persiapan pengadaan sudah diselesaikan dan terkunci.'
        );
    }

    private function validateWithItem(Request $request, $procurementPackage): array
    {
        $items = $procurementPackage->technicalSpecification?->items ?? collect();

        $validated = $request->validate([
            'technical_specification_item_id' => ['required', 'integer', 'in:' . $items->pluck('id')->implode(',')],
            'nama_produk_etalase' => ['nullable', 'string', 'max:255'],
            'nama_pelaku_usaha' => ['nullable', 'string', 'max:255'],
            'harga_satuan' => ['required', 'numeric', 'min:0'],
            'link_produk' => ['nullable', 'string'],
        ]);

        $item = $items->firstWhere('id', (int) $validated['technical_specification_item_id']);

        unset($validated['technical_specification_item_id']);

        return [$validated, $item];
    }

    private function backToPanel(Package $package, string $message): RedirectResponse
    {
        return redirect()
            ->route('kabid.procurement-packages.show', $package)
            ->with('success', $message)
            ->with('panel', 4);
    }
}
