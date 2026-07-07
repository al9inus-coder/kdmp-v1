<?php

namespace App\Http\Controllers\Kabid;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PriceReference;
use App\Models\ProcurementPackage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PriceReferenceController extends Controller
{
    /**
     * Ambil data produk dari link katalog.inaproc.id untuk mengisi form
     * referensi harga secara otomatis (nama produk, penyedia, harga ber-PPN).
     */
    public function fetchFromCatalog(Request $request, Package $package): JsonResponse
    {
        Gate::authorize('view', $package);

        $validated = $request->validate([
            'url' => ['required', 'url', 'max:2048'],
        ]);

        $url = $validated['url'];

        // Batasi host untuk mencegah SSRF — hanya katalog resmi.
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        if ($host !== 'katalog.inaproc.id') {
            return response()->json(['message' => 'Link harus dari katalog.inaproc.id.'], 422);
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'id,en;q=0.9',
            ])->timeout(15)->retry(1, 500)->get($url);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Gagal mengakses katalog. Silakan coba lagi.'], 502);
        }

        if (!$response->successful()) {
            return response()->json([
                'message' => 'Halaman katalog tidak dapat diakses (HTTP ' . $response->status() . ').',
            ], 502);
        }

        $html = $response->body();

        // Nama produk dari <title>: "Jual <NAMA> | INAPROC Katalog Elektronik"
        $namaProduk = null;
        if (preg_match('/<title>\s*(?:Jual\s+)?(.*?)\s*\|\s*INAPROC/is', $html, $m)) {
            $namaProduk = trim(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }

        // Harga dengan PPN (maxPriceWithTax, fallback minPriceWithTax).
        $harga = null;
        if (preg_match('/maxPriceWithTax[^0-9]{0,8}(\d+)/', $html, $m)) {
            $harga = (int) $m[1];
        } elseif (preg_match('/minPriceWithTax[^0-9]{0,8}(\d+)/', $html, $m)) {
            $harga = (int) $m[1];
        }

        // Nama penyedia dari segmen pertama path URL (slug), di-title-case.
        $namaPelaku = null;
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
        $segments = array_values(array_filter(explode('/', $path)));
        if (!empty($segments[0])) {
            $namaPelaku = collect(explode('-', $segments[0]))
                ->map(fn ($w) => Str::ucfirst($w))
                ->implode(' ');
        }

        if (!$namaProduk && $harga === null) {
            return response()->json([
                'message' => 'Data produk tidak ditemukan di halaman tersebut. Periksa link atau isi manual.',
            ], 422);
        }

        return response()->json([
            'nama_produk_etalase' => $namaProduk,
            'nama_pelaku_usaha'   => $namaPelaku,
            'harga_satuan'        => $harga,
            'link_produk'         => $url,
        ]);
    }

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
