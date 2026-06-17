<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\ProcurementPackage;
use App\Services\AI\OpenAIService;
use App\Models\AiPrompt;
use App\Models\Skpd;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\TechnicalSpecification;
use App\Models\TechnicalSpecificationItem;
use Illuminate\Support\Facades\DB;

class ProcurementPackageController extends Controller
{
    public function index(): View
    {
        $procurementPackages = ProcurementPackage::query()
            ->with([
                'package.program',
                'package.fiscalYear',
                'creator',
            ])
            ->orderByDesc('id')
            ->paginate(15);

        return view('procurement-packages.index', compact('procurementPackages'));
    }

    public function store(Package $package): RedirectResponse
    {
        if ($package->status !== 'approved') {
            return back()->with('error', 'Hanya package berstatus approved yang dapat dibuatkan Paket Pengadaan.');
        }

        $existing = $package->procurementPackage;

        if ($existing) {
            return redirect()
                ->route('procurement-packages.show', $package)
                ->with('warning', 'Paket Pengadaan untuk package ini sudah ada.');
        }

        $procurementPackage = ProcurementPackage::create([
            'package_id' => $package->id,
            'number' => null,
            'status' => 'draft',
            'created_by' => Auth::id(),
        ]);

        return redirect()
            ->route('procurement-packages.show', $package)
            ->with('success', 'Paket Pengadaan berhasil dibuat.');
    }

    public function show(Package $package): View
    {
        $procurementPackage = $package->procurementPackage;

        abort_if(!$procurementPackage, 404);

        $procurementPackage->load([
            'package.fiscalYear',
            'package.program',
            'package.activity',
            'package.subActivity',
            'package.account',
            'creator',
            'technicalSpecification.items',
            'procurementRequest',
        ]);

        $procurementPackage->loadCount('priceReferences');

        return view(
            'procurement-packages.show',
            compact('procurementPackage')
        );
    }

    public function updateMeta(Request $request, ProcurementPackage $procurementPackage)
    {
        $data = $request->validate([
            'nama_ppk' => 'nullable|string|max:255',
            'pangkat_gol_ppk' => 'nullable|string|max:255',
            'nip_ppk' => 'nullable|string|max:255',
            'no_telp_ppk' => 'nullable|string|max:255',
            'email_ppk' => 'nullable|email|max:255',
            'user_ppk' => 'nullable|string|max:255',
            'npwp_instansi' => 'nullable|string|max:255',
            'jenis_kontrak' => 'nullable|string|max:255',
            'jangka_waktu_nilai' => 'nullable|integer|min:0',
            'jangka_waktu_satuan' => 'nullable|in:hari,bulan,tahun',
            'tanggal_barang_diterima' => 'nullable|date',
            'ada_garansi' => 'nullable|boolean',    
            'garansi_nilai' => 'nullable|integer',
            'garansi_satuan' => 'nullable|in:hari,bulan,tahun',
            'layanan_purna_jual' => 'nullable|boolean',
            'items' => 'nullable|array',
            'items.*.nama_barang_jasa' => 'required_with:items|string|max:255',
            'items.*.spesifikasi' => 'nullable|string',
            'items.*.volume' => 'nullable|numeric',
            'items.*.satuan' => 'nullable|string|max:50',
            'items.*.harga_satuan_dpa' => 'nullable|string|max:50',
            'items.*.pdn' => 'nullable|boolean',
            'items.*.tkdn' => 'nullable|numeric|min:0|max:100',
            'items.*.kode_mak' => 'nullable|string|max:255',
        ]);

        $procurementPackage->update($data);

        $technicalSpecification = $procurementPackage->technicalSpecification;

        if (!$technicalSpecification) {
            $technicalSpecification = TechnicalSpecification::create([
                'procurement_package_id' => $procurementPackage->id,
            ]);
        }

        $technicalSpecification->items()->delete();

        foreach ($request->input('items', []) as $index => $item) {
            $technicalSpecification->items()->create([
                'nama_barang_jasa' => $item['nama_barang_jasa'] ?? null,
                'spesifikasi' => $item['spesifikasi'] ?? null,
                'volume' => $item['volume'] ?? 0,
                'satuan' => $item['satuan'] ?? null,
                'harga_satuan_dpa' =>empty($item['harga_satuan_dpa']) ? null: (float) str_replace('.', '', $item['harga_satuan_dpa']),
                'pdn' => !empty($item['pdn']),
                'tkdn' => $item['tkdn'] ?? null,
                'kode_mak' => $item['kode_mak'] ?? null,
                'urutan' => $index,
            ]);
        }

        return back()->with('success', 'Informasi paket berhasil disimpan.');
    }

    public function generateDraft(ProcurementPackage $procurementPackage, OpenAIService $openai)
    {
        $procurementPackage->load([
            'package.program',
            'package.activity',
            'package.subActivity',
            'technicalSpecification.items',
        ]);
        
        $package = $procurementPackage->package;
        $technicalSpecification = $procurementPackage->technicalSpecification;

        if (!$technicalSpecification) {
            return back()->with('error', 'Spesifikasi teknis belum dibuat.');
        }

        $items = [];
        foreach ($technicalSpecification->items as $item) {
            $items[] = [
                'nama_barang_jasa' => $item->nama_barang_jasa,
                'spesifikasi' => $item->spesifikasi,
                'volume' => $item->volume,
                'satuan' => $item->satuan,
                'pdn' => $item->pdn,
                'tkdn' => $item->tkdn,
            ];
        }

        $prompt = $this->buildTechnicalSpecificationPrompt($package, $items);
        
//        dd($prompt);
        $draft = $openai->generateTechnicalSpecificationJson($prompt);

        $technicalSpecification->update([
            'latar_belakang' => $draft['latar_belakang'] ?? null,
            'maksud' => $draft['maksud'] ?? null,
            'target_sasaran' => $draft['target_sasaran'] ?? null,
            'uraian_pekerjaan' => $draft['uraian_pekerjaan'] ?? null,
        ]);

        return redirect()
            ->route(
                'procurement-packages.technical-specifications.show',
                $procurementPackage->package
            )
            ->with(
                'success',
                'Dokumen Spesifikasi Teknis berhasil dibuat.'
            );
    }

    private function buildTechnicalSpecificationPrompt(Package $package, array $items): string
    {
        $skpd = Skpd::first();
        $promptTemplate = AiPrompt::where(
            'code',
            'technical_specification'
        )->where(
            'is_active',
            true
        )->first();
        $template = $promptTemplate->prompt;
        $prompt = str_replace(
            [
                '{SKPD}',
                '{NAMA_PAKET}',
                '{PROGRAM}',
                '{KEGIATAN}',
                '{SUB_KEGIATAN}',
                '{JENIS_PENGADAAN}',
                '{ITEMS}',
            ],
            [
                $skpd->nama_skpd,
                $package->nama_paket,
                $package->program?->nama,
                $package->activity?->nama,
                $package->subActivity?->nama,
                $package->jenis_pengadaan,
                json_encode($items, JSON_PRETTY_PRINT),
            ],
            $template
        );  
        return $prompt;
    }
}