<?php

namespace App\Http\Controllers\Kabid;

use App\Http\Controllers\Controller;
use App\Models\AiPrompt;
use App\Models\Package;
use App\Models\ProcurementPackage;
use App\Models\Skpd;
use App\Models\TechnicalSpecification;
use App\Services\AI\OpenAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ProcurementPackageController extends Controller
{
    public function show(Package $package)
    {
        Gate::authorize('view', $package);

        $procurementPackage = $package->procurementPackage;

        abort_if(!$procurementPackage, 404);

        $procurementPackage->load([
            'package.fiscalYear',
            'package.program',
            'package.activity',
            'package.subActivity',
            'package.account',
            'package.travelOrders.personnels.employee',
            'creator',
            'technicalSpecification.items',
            'procurementRequest',
            'priceReferences',
            'externalRecords',
        ]);

        $procurementPackage->loadCount('priceReferences');

        // Pastikan data PPK terisi (snapshot dari SKPD) jika masih kosong
        $procurementPackage->syncPpkFromSkpd();

        $aiPrompt = AiPrompt::where('code', 'technical_specification')
            ->where('is_active', true)
            ->first();

        if ($this->isTravelSwakelolaPackage($package)) {
            $travelStats = $this->buildTravelStats($package);

            return view('kabid.procurement-packages.show-swakelola-travel', compact(
                'procurementPackage',
                'aiPrompt',
                'travelStats'
            ));
        }

        if ($this->isLemburSwakelolaPackage($package)) {
            $lemburStats = $this->buildLemburStats($package);

            return view('kabid.procurement-packages.show-swakelola-lembur', compact(
                'procurementPackage',
                'aiPrompt',
                'lemburStats'
            ));
        }

        if ($package->metode_pengadaan === 'Dikecualikan') {
            return view('kabid.procurement-packages.show-dikecualikan', compact('procurementPackage'));
        }

        return view('kabid.procurement-packages.show', compact('procurementPackage', 'aiPrompt'));
    }

    private function isTravelSwakelolaPackage(Package $package): bool
    {
        $jenisPengadaan = str($package->jenis_pengadaan ?? '')->lower();
        $accountName = str($package->account?->nama ?? '')->lower();

        return $jenisPengadaan->contains('swakelola')
            && $accountName->contains('perjalanan dinas');
    }

    private function isLemburSwakelolaPackage(Package $package): bool
    {
        $jenisPengadaan = str($package->jenis_pengadaan ?? '')->lower();
        $accountName = str($package->account?->nama ?? '')->lower();

        return $jenisPengadaan->contains('swakelola')
            && $accountName->contains('lembur');
    }

    private function buildLemburStats(Package $package): array
    {
        $sbuRates = \App\Models\SbuLembur::all();
        $overtimes = \App\Models\Overtime::where('package_id', $package->id)
            ->with('details.employee')
            ->get();

        $months = [];
        $totalRealisasi = 0.0;
        $bulanTerisi = 0;

        for ($num = 1; $num <= 12; $num++) {
            $overtime = $overtimes->firstWhere('bulan', $num);
            $total = 0.0;

            if ($overtime) {
                $total = (float) $overtime->calculateTotalRealisasi($sbuRates);
                $totalRealisasi += $total;
                $bulanTerisi++;
            }

            $months[$num] = [
                'exists' => (bool) $overtime,
                'total' => $total,
                'is_locked' => $overtime ? (bool) $overtime->is_locked : false,
            ];
        }

        $pagu = (float) ($package->pagu ?? 0);
        $percentage = $pagu > 0 ? min(100, ($totalRealisasi / $pagu) * 100) : 0;

        return [
            'pagu' => $pagu,
            'total_realisasi' => $totalRealisasi,
            'sisa_anggaran' => $pagu - $totalRealisasi,
            'percentage' => $percentage,
            'bulan_terisi' => $bulanTerisi,
            'months' => $months,
        ];
    }

    private function buildTravelStats(Package $package): array
    {
        // Hanya perjalanan dinas dengan SPJ (biaya rampung) yang sudah disetujui yang dihitung
        // sebagai realisasi — konsisten dengan daftar yang ditampilkan di halaman ini.
        $travelOrders = $package->travelOrders
            ->filter(fn ($travelOrder) => $travelOrder->spjStatus() === \App\Models\TravelOrder::SPJ_APPROVED);

        $totalRealisasi = $travelOrders->sum(function ($travelOrder) {
            return $travelOrder->personnels->sum(function ($personnel) {
                return (float) $personnel->uang_harian
                    + (float) $personnel->biaya_transport
                    + (float) ($personnel->biaya_taksi ?? 0)
                    + (float) $personnel->biaya_penginapan
                    + (float) $personnel->biaya_representasi;
            });
        });

        $pagu = (float) ($package->pagu ?? 0);
        $percentage = $pagu > 0 ? min(100, ($totalRealisasi / $pagu) * 100) : 0;

        return [
            'pagu' => $pagu,
            'total_realisasi' => $totalRealisasi,
            'sisa_anggaran' => $pagu - $totalRealisasi,
            'percentage' => $percentage,
            'total_orders' => $travelOrders->count(),
            'total_personnels' => $travelOrders->sum(fn ($travelOrder) => $travelOrder->personnels->count()),
        ];
    }

    /**
     * Persiapan yang sudah diselesaikan terkunci untuk Kabid;
     * hanya Admin (nanti) yang dapat membukanya kembali.
     */
    private function assertPreparationEditable(?ProcurementPackage $procurementPackage): void
    {
        abort_if(!$procurementPackage, 404);

        abort_if(
            $procurementPackage->workflow_status !== ProcurementPackage::WORKFLOW_DRAFT,
            403,
            'Persiapan pengadaan sudah diselesaikan dan terkunci.'
        );
    }

    public function completePreparation(Package $package)
    {
        Gate::authorize('view', $package);

        $procurementPackage = $package->procurementPackage;

        $this->assertPreparationEditable($procurementPackage);

        $procurementPackage->loadMissing('technicalSpecification.items', 'procurementRequest');
        $procurementPackage->loadCount('priceReferences');

        $ts = $procurementPackage->technicalSpecification;

        $lengkap = filled($procurementPackage->jenis_kontrak)
            && filled($procurementPackage->jangka_waktu_nilai)
            && $ts && $ts->items->isNotEmpty()
            && filled($ts->latar_belakang) && filled($ts->uraian_pekerjaan)
            && $procurementPackage->price_references_count > 0
            && $procurementPackage->procurementRequest;

        if (!$lengkap) {
            return redirect()
                ->route('kabid.procurement-packages.show', $package)
                ->with('error', 'Masih ada langkah persiapan yang belum lengkap. Periksa kembali daftar periksa.')
                ->with('panel', request('next_panel', 6));
        }

        $procurementPackage->update([
            'workflow_status' => ProcurementPackage::WORKFLOW_PROVIDER_SELECTION,
        ]);

        return redirect()
            ->route('kabid.procurement-packages.show', $package)
            ->with('success', 'Persiapan pengadaan selesai. Paket masuk tahap Pemilihan Penyedia.')
            ->with('panel', request('next_panel', 6));
    }

    public function updatePrompt(Request $request, Package $package)
    {
        Gate::authorize('view', $package);

        $request->validate([
            'prompt' => 'required|string',
        ]);

        AiPrompt::updateOrCreate(
            ['code' => 'technical_specification'],
            [
                'name' => 'Spesifikasi Teknis',
                'prompt' => $request->prompt,
                'is_active' => true,
            ]
        );

        return redirect()
            ->route('kabid.procurement-packages.show', $package)
            ->with('success', 'Prompt AI berhasil diperbarui.')
            ->with('panel', request('next_panel', 3));
    }

    public function updateContract(Request $request, Package $package)
    {
        Gate::authorize('view', $package);

        $procurementPackage = $package->procurementPackage;

        $this->assertPreparationEditable($procurementPackage);

        $data = $request->validate([
            'jenis_kontrak' => 'nullable|string|max:255',
            'jangka_waktu_nilai' => 'nullable|integer|min:1',
            'jangka_waktu_satuan' => 'nullable|string|in:hari,minggu,bulan,tahun',
            'tanggal_barang_diterima' => 'nullable|date',
            'ada_garansi' => 'nullable|boolean',
            'garansi_nilai' => 'nullable|integer|min:1',
            'garansi_satuan' => 'nullable|string|in:hari,minggu,bulan,tahun',
            'layanan_purna_jual' => 'nullable|boolean',
        ]);

        if (empty($data['ada_garansi'])) {
            $data['garansi_nilai'] = null;
            $data['garansi_satuan'] = null;
        }

        $procurementPackage->update($data);

        return redirect()
            ->route('kabid.procurement-packages.show', $package)
            ->with('success', 'Detail kontrak & pelaksanaan berhasil disimpan.')
            ->with('panel', request('next_panel', 1));
    }

    public function updateItems(Request $request, Package $package)
    {
        Gate::authorize('view', $package);

        $procurementPackage = $package->procurementPackage;

        $this->assertPreparationEditable($procurementPackage);

        $data = $request->validate([
            'items' => 'nullable|array',
            'items.*.nama_barang_jasa' => 'nullable|string|max:255',
            'items.*.spesifikasi' => 'nullable|string',
            'items.*.volume' => 'nullable|numeric|min:0',
            'items.*.satuan' => 'nullable|string|max:255',
            'items.*.harga_satuan_dpa' => 'nullable|numeric|min:0',
            'items.*.pdn' => 'nullable|boolean',
            'items.*.tkdn' => 'nullable|numeric|min:0|max:100',
            'items.*.kode_mak' => 'nullable|string|max:255',
        ]);

        $technicalSpecification = $procurementPackage->technicalSpecification
            ?? TechnicalSpecification::create([
                'procurement_package_id' => $procurementPackage->id,
            ]);

        $technicalSpecification->items()->delete();

        $urutan = 0;
        foreach ($data['items'] ?? [] as $item) {
            // Lewati baris yang sepenuhnya kosong
            if (blank($item['nama_barang_jasa'] ?? null) && blank($item['spesifikasi'] ?? null)) {
                continue;
            }

            $technicalSpecification->items()->create([
                'nama_barang_jasa' => $item['nama_barang_jasa'] ?? null,
                'spesifikasi' => $item['spesifikasi'] ?? null,
                'volume' => $item['volume'] ?? 0,
                'satuan' => $item['satuan'] ?? null,
                'harga_satuan_dpa' => $item['harga_satuan_dpa'] ?? null,
                'pdn' => !empty($item['pdn']),
                'tkdn' => $item['tkdn'] ?? null,
                'kode_mak' => $item['kode_mak'] ?? null,
                'urutan' => $urutan++,
            ]);
        }

        return redirect()
            ->route('kabid.procurement-packages.show', $package)
            ->with('success', 'Rincian barang/jasa berhasil disimpan.')
            ->with('panel', request('next_panel', 2));
    }

    public function updateRequest(Request $request, Package $package)
    {
        Gate::authorize('view', $package);

        $procurementPackage = $package->procurementPackage;

        $this->assertPreparationEditable($procurementPackage);

        $data = $request->validate([
            'nomor_surat' => 'nullable|string|max:255',
            'tanggal_surat' => 'nullable|date',
            'nama_pejabat_pengadaan' => 'nullable|string|max:255',
            'nama_penyedia' => 'nullable|string|max:255',
            'alasan_pemilihan_penyedia' => 'nullable|string',
        ]);

        $procurementPackage->procurementRequest()->updateOrCreate(
            [],
            array_merge($data, [
                'updated_by' => Auth::id(),
                'created_by' => $procurementPackage->procurementRequest?->created_by ?? Auth::id(),
            ])
        );

        return redirect()
            ->route('kabid.procurement-packages.show', $package)
            ->with('success', 'Surat Permohonan berhasil disimpan.')
            ->with('panel', request('next_panel', 5));
    }

    public function updateSpecification(Request $request, Package $package)
    {
        Gate::authorize('view', $package);

        $procurementPackage = $package->procurementPackage;

        $this->assertPreparationEditable($procurementPackage);

        $data = $request->validate([
            'latar_belakang' => 'nullable|string',
            'maksud' => 'nullable|array',
            'maksud.*' => 'nullable|string',
            'target_sasaran' => 'nullable|array',
            'target_sasaran.*' => 'nullable|string',
            'uraian_pekerjaan' => 'nullable|string',
            'tanggal' => 'nullable|date',
        ]);

        $technicalSpecification = $procurementPackage->technicalSpecification
            ?? TechnicalSpecification::create([
                'procurement_package_id' => $procurementPackage->id,
            ]);

        $technicalSpecification->update(array_merge($data, [
            'updated_by' => Auth::id(),
        ]));

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Spesifikasi teknis berhasil disimpan.']);
        }

        return redirect()
            ->route('kabid.procurement-packages.show', $package)
            ->with('success', 'Spesifikasi teknis berhasil disimpan.')
            ->with('panel', request('next_panel', 3));
    }

    public function generateSpecification(Package $package, OpenAIService $openai)
    {
        Gate::authorize('view', $package);

        set_time_limit(120);

        $procurementPackage = $package->procurementPackage;

        $this->assertPreparationEditable($procurementPackage);

        $procurementPackage->load(['package.program', 'package.activity', 'package.subActivity', 'technicalSpecification.items']);

        $technicalSpecification = $procurementPackage->technicalSpecification;

        if (!$technicalSpecification || $technicalSpecification->items->isEmpty()) {
            return redirect()
                ->route('kabid.procurement-packages.show', $package)
                ->with('error', 'Isi rincian Barang/Jasa terlebih dahulu sebelum membuat draf AI.')
                ->with('panel', request('next_panel', 2));
        }

        $items = $technicalSpecification->items->map(fn($item) => [
            'nama_barang_jasa' => $item->nama_barang_jasa,
            'spesifikasi' => $item->spesifikasi,
            'volume' => $item->volume,
            'satuan' => $item->satuan,
            'pdn' => $item->pdn,
            'tkdn' => $item->tkdn,
        ])->all();

        try {
            $prompt = $this->buildSpecificationPrompt($procurementPackage, $items);

            $draft = $openai->generateTechnicalSpecificationJson($prompt);

            $technicalSpecification->update([
                'latar_belakang' => $draft['latar_belakang'] ?? null,
                'maksud' => $draft['maksud'] ?? null,
                'target_sasaran' => $draft['target_sasaran'] ?? null,
                'uraian_pekerjaan' => $draft['uraian_pekerjaan'] ?? null,
                'updated_by' => Auth::id(),
            ]);

            return redirect()
                ->route('kabid.procurement-packages.show', $package)
                ->with('success', 'Draf Spesifikasi Teknis berhasil dibuat oleh AI. Silakan periksa dan sesuaikan.')
                ->with('panel', request('next_panel', 3));
        } catch (\Exception $e) {
            return redirect()
                ->route('kabid.procurement-packages.show', $package)
                ->with('error', 'Gagal menghasilkan dokumen. Server AI mungkin sedang sibuk. Detail: ' . $e->getMessage())
                ->with('panel', request('next_panel', 3));
        }
    }

    private function buildSpecificationPrompt($procurementPackage, array $items): string
    {
        $package = $procurementPackage->package;
        $skpd = Skpd::first();

        $promptTemplate = AiPrompt::where('code', 'technical_specification')
            ->where('is_active', true)
            ->first();

        if (!$promptTemplate) {
            throw new \Exception('Template Prompt untuk Spesifikasi Teknis belum diatur atau tidak aktif.');
        }

        $prompt = str_replace(
            ['{SKPD}', '{NAMA_PAKET}', '{PROGRAM}', '{KEGIATAN}', '{SUB_KEGIATAN}', '{JENIS_PENGADAAN}', '{ITEMS}'],
            [
                $skpd?->nama ?? '-',
                $package->nama_paket,
                $package->program?->nama,
                $package->activity?->nama,
                $package->subActivity?->nama,
                $package->jenis_pengadaan,
                json_encode($items, JSON_PRETTY_PRINT),
            ],
            $promptTemplate->prompt
        );

        // Garansi dibaca dari procurement package (di controller umum keliru membaca dari technical specification)
        $garansiText = 'Tidak ada garansi yang diperlukan untuk paket ini.';
        if ($procurementPackage->ada_garansi && filled($procurementPackage->garansi_nilai)) {
            $garansiText = 'Penyedia WAJIB memberikan garansi selama '
                . $procurementPackage->garansi_nilai . ' '
                . ucfirst($procurementPackage->garansi_satuan ?? 'hari') . '.';
        }

        $prompt .= "\n\nINFORMASI GARANSI:\n";
        $prompt .= $garansiText . "\n";
        $prompt .= "Pada bagian 'uraian_pekerjaan', JIKA ADA GARANSI, wajib sebutkan kewajiban garansi tersebut secara eksplisit. JIKA TIDAK ADA GARANSI, JANGAN PERNAH MENYEBUTKAN KATA GARANSI DALAM URAIAN PEKERJAAN ATAU BAGIAN MANAPUN.\n";

        $prompt .= "\n\nABAIKAN FORMAT JSON PADA INSTRUKSI DI ATAS. ANDA WAJIB MENGEMBALIKAN PURE JSON DENGAN FORMAT BERIKUT:\n";
        $prompt .= "{\n";
        $prompt .= '  "latar_belakang": "...",' . "\n";
        $prompt .= '  "maksud": {' . "\n";
        $prompt .= '    "Maksud": "...",' . "\n";
        $prompt .= '    "Tujuan": "..."' . "\n";
        $prompt .= '  },' . "\n";
        $prompt .= '  "target_sasaran": {' . "\n";
        $prompt .= '    "Target": "...",' . "\n";
        $prompt .= '    "Sasaran": "..."' . "\n";
        $prompt .= '  },' . "\n";
        $prompt .= '  "uraian_pekerjaan": "..."' . "\n";
        $prompt .= "}";

        return $prompt;
    }
}
