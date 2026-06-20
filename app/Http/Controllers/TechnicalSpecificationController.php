<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\ProcurementPackage;
use App\Models\TechnicalSpecification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
class TechnicalSpecificationController extends Controller
{
    public function create(ProcurementPackage $procurementPackage): View|RedirectResponse
    {
        if ($procurementPackage->technicalSpecification) {
            return redirect()->route(
                'procurement-packages.technical-specification.edit',
                $procurementPackage
            );
        }

        $technicalSpecification = new TechnicalSpecification();

        return view('technical-specifications.create', compact(
            'procurementPackage',
            'technicalSpecification'
        ));
    }

    public function store(Request $request, ProcurementPackage $procurementPackage): RedirectResponse
    {
        if ($procurementPackage->technicalSpecification) {
            return redirect()->route(
                'procurement-packages.technical-specification.show',
                $procurementPackage
            )->with('warning', 'Spesifikasi Teknis sudah tersedia.');
        }

        $validated = $this->validateRequest($request);
        
        $items = $this->normalizeItems($validated['items'] ?? []);
        unset($validated['items']);

        DB::transaction(function () use ($procurementPackage, $validated, $items): void {
            $technicalSpecification = $procurementPackage->technicalSpecification()->create(array_merge(
                $validated,
                [
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]
            ));

            $technicalSpecification->items()->createMany($items);
        });

        return redirect()
            ->route('procurement-packages.technical-specification.show', $procurementPackage)
            ->with('success', 'Spesifikasi Teknis berhasil dibuat.');
    }
    public function show(Package $package): View
    {
        $procurementPackage =
            $package->procurementPackage;

        abort_if(!$procurementPackage, 404);

        $technicalSpecification =
            $procurementPackage->technicalSpecification;

        abort_if(!$technicalSpecification, 404);

        $technicalSpecification->load([
            'items',
            'procurementPackage.package.program',
            'procurementPackage.package.activity',
            'procurementPackage.package.subActivity',
            'procurementPackage.package.fiscalYear',
        ]);

        return view(
            'technical-specifications.show',
            compact(
                'technicalSpecification',
                'procurementPackage'
            )
        );
    }

    public function edit(ProcurementPackage $procurementPackage): View|RedirectResponse
    {
        $technicalSpecification = $procurementPackage->technicalSpecification;

        if (!$technicalSpecification) {
            return redirect()->route(
                'procurement-packages.technical-specification.create',
                $procurementPackage
            );
        }

        $technicalSpecification->load('items');

        return view('technical-specifications.edit', compact(
            'procurementPackage',
            'technicalSpecification'
        ));
    }

    public function update(Request $request, ProcurementPackage $procurementPackage)
    {
        $technicalSpecification = $procurementPackage->technicalSpecification;

        if (!$technicalSpecification) {
            return redirect()->route(
                'procurement-packages.technical-specification.create',
                $procurementPackage
            );
        }

        $validated = $this->validateRequest($request);
        $hasItems =
            array_key_exists('items', $validated);

        $items = $this->normalizeItems(
            $validated['items'] ?? []
        );

        unset($validated['items']);

        DB::transaction(function () use ($procurementPackage, $technicalSpecification, $validated, $items, $hasItems): void {
            // Sync ke ProcurementPackage
            $syncData = [];
            if (array_key_exists('garansi_nilai', $validated)) $syncData['garansi_nilai'] = $validated['garansi_nilai'];
            if (array_key_exists('garansi_satuan', $validated)) $syncData['garansi_satuan'] = $validated['garansi_satuan'];
            if (array_key_exists('layanan_purna_jual', $validated)) $syncData['layanan_purna_jual'] = $validated['layanan_purna_jual'];
            if (array_key_exists('jangka_waktu', $validated)) $syncData['jangka_waktu_nilai'] = $validated['jangka_waktu'];
            if (array_key_exists('jangka_waktu_jenis', $validated)) $syncData['jangka_waktu_satuan'] = $validated['jangka_waktu_jenis'] === 'pengiriman_barang' ? 'hari' : 'bulan'; // or whatever mapping is appropriate, actually we will fix form fields later
            if (array_key_exists('jenis_kontrak', $validated)) $syncData['jenis_kontrak'] = $validated['jenis_kontrak'];
            if (array_key_exists('npwp_instansi', $validated)) $syncData['npwp_instansi'] = $validated['npwp_instansi'];
            if (array_key_exists('nama_ppk', $validated)) $syncData['nama_ppk'] = $validated['nama_ppk'];
            if (array_key_exists('pangkat_gol_ppk', $validated)) $syncData['pangkat_gol_ppk'] = $validated['pangkat_gol_ppk'];
            if (array_key_exists('nip_ppk', $validated)) $syncData['nip_ppk'] = $validated['nip_ppk'];
            if (array_key_exists('no_telp_ppk', $validated)) $syncData['no_telp_ppk'] = $validated['no_telp_ppk'];
            if (array_key_exists('email_ppk', $validated)) $syncData['email_ppk'] = $validated['email_ppk'];

            if (!empty($syncData)) {
                $procurementPackage->update($syncData);
            }

            // Remove synced fields from validated so they don't get saved to technical_specifications
            $fieldsToRemove = [
                'jangka_waktu', 'jangka_waktu_jenis', 'garansi_nilai', 'garansi_satuan', 
                'layanan_purna_jual', 'jenis_kontrak', 'npwp_instansi', 'nama_ppk', 
                'pangkat_gol_ppk', 'nip_ppk', 'no_telp_ppk', 'email_ppk'
            ];
            foreach ($fieldsToRemove as $field) {
                unset($validated[$field]);
            }

            $technicalSpecification->update(array_merge(
                $validated,
                ['updated_by' => Auth::id()]
            ));

            if ($hasItems) {
                $technicalSpecification->items()->delete();
                $technicalSpecification->items()->createMany(
                    $items
                );
            }
        });

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Spesifikasi Teknis berhasil diperbarui.']);
        }

        return redirect()
            ->route(
                'procurement-packages.technical-specifications.show',
                $technicalSpecification
                    ->procurementPackage
                    ->package
            )
            ->with(
                'success',
                'Spesifikasi Teknis berhasil diperbarui.'
            );
    }
    /**
     * @return array<string, mixed>
     */
    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'latar_belakang' => ['nullable', 'string'],
            'maksud' => ['nullable', 'array'],
            'maksud.*' => ['nullable', 'string'],
            'target_sasaran' => ['nullable', 'array'],
            'target_sasaran.*' => ['nullable', 'string'],
            'uraian_pekerjaan' => ['nullable', 'string'],
            'jangka_waktu' => ['nullable', 'integer', 'min:0'],
            'jangka_waktu_jenis' => ['nullable', Rule::in(['pengiriman_barang', 'pekerjaan_jasa'])],
            'garansi_nilai' => ['nullable', 'integer', 'min:0'],
            'garansi_satuan' => ['nullable', Rule::in(['hari', 'bulan', 'tahun'])],
            'layanan_purna_jual' => ['nullable', 'boolean'],
            'jenis_kontrak' => ['nullable', Rule::in([
                'Harga Satuan',
                'Lump Sum',
                'Gabungan Lump Sum dan Harga Satuan',
                'Payung',
                'Turnkey',
                'Kontrak Kinerja',
            ])],
            'npwp_instansi' => ['nullable', 'string', 'max:255'],
            'nama_ppk' => ['nullable', 'string', 'max:255'],
            'pangkat_gol_ppk' => ['nullable', 'string', 'max:255'],
            'nip_ppk' => ['nullable', 'string', 'max:255'],
            'no_telp_ppk' => ['nullable', 'string', 'max:255'],
            'email_ppk' => ['nullable', 'email', 'max:255'],
            'items' => ['nullable', 'array'],
            'items.*.nama_barang_jasa' => ['nullable', 'string', 'max:255'],
            'items.*.spesifikasi' => ['nullable', 'string'],
            'items.*.volume' => ['nullable', 'numeric', 'min:0'],
            'items.*.satuan' => ['nullable', 'string', 'max:255'],
            'items.*.harga_satuan_dpa' => 'nullable|string|max:50',
            'items.*.pdn' => ['nullable', 'boolean'],
            'items.*.tkdn' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.kode_mak' => ['nullable', 'string', 'max:255'],
        ]);
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    private function normalizeItems(array $items): array
    {
        $normalized = [];

        foreach ($items as $item) {
            $namaBarangJasa = trim((string) ($item['nama_barang_jasa'] ?? ''));

            if ($namaBarangJasa === '') {
                continue;
            }

            $normalized[] = [
                'nama_barang_jasa' => $namaBarangJasa,
                'spesifikasi' => $item['spesifikasi'] ?? null,
                'volume' => (float) ($item['volume'] ?? 0),
                'satuan' => $item['satuan'] ?? null,
                'harga_satuan_dpa' => (
    $item['harga_satuan_dpa'] ?? ''
) === ''
    ? null
    : (float) str_replace('.', '', $item['harga_satuan_dpa']),
                'pdn' => (bool) ($item['pdn'] ?? false),
                'tkdn' => ($item['tkdn'] ?? '') === '' ? null : (float) $item['tkdn'],
                'kode_mak' => $item['kode_mak'] ?? null,
                'urutan' => count($normalized) + 1,
            ];
        }

        return $normalized;
    }

    public function editByTechnicalSpecification(
        TechnicalSpecification $technicalSpecification
    )
    {
        $procurementPackage =
            $technicalSpecification->procurementPackage;

        return $this->edit($procurementPackage);
    }

    public function updateByTechnicalSpecification(
        Request $request,
        TechnicalSpecification $technicalSpecification
    )
    {
        $procurementPackage =
            $technicalSpecification->procurementPackage;

        return $this->update(
            $request,
            $procurementPackage
        );
    }
    
   public function print(
        TechnicalSpecification $technicalSpecification
    )
    {
        $technicalSpecification->load([
            'items',
            'procurementPackage.package.program',
            'procurementPackage.package.activity',
            'procurementPackage.package.subActivity',
            'procurementPackage.package.account',
            'procurementPackage.package.fiscalYear',
        ]);

        $procurementPackage =
            $technicalSpecification->procurementPackage;

        $isBarang =
            ($procurementPackage->package->jenis_pengadaan ?? '')
            === 'Barang';

        $jangkaWaktuNilai = $procurementPackage->jangka_waktu_nilai ?? null;
        $jangkaWaktuSatuan = $procurementPackage->jangka_waktu_satuan ?? 'hari';
        $garansiNilai = $procurementPackage->garansi_nilai;
        $garansiSatuan = $procurementPackage->garansi_satuan;
        $layananPurnaJual = $procurementPackage->layanan_purna_jual;

        return view(
            'technical-specifications.pdf',
            compact(
                'technicalSpecification',
                'procurementPackage',
                'isBarang',
                'jangkaWaktuNilai',
                'jangkaWaktuSatuan',
                'garansiNilai',
                'garansiSatuan',
                'layananPurnaJual'
            )
        );
    }
}
