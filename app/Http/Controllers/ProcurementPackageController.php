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
    public function index(Request $request): View
    {
        $query = ProcurementPackage::query()
            ->with([
                'package.program',
                'package.fiscalYear',
                'creator',
            ]);

        if ($request->filled('program_id')) {
            $query->whereHas('package', function ($q) use ($request) {
                $q->where('program_id', $request->program_id);
            });
        }

        if ($request->filled('workflow_status')) {
            $query->where('workflow_status', $request->workflow_status);
        }

        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'draft') {
                $query->where('workflow_status', \App\Models\ProcurementPackage::WORKFLOW_DRAFT);
            } elseif ($status === 'persiapan') {
                $query->where('workflow_status', \App\Models\ProcurementPackage::WORKFLOW_PROVIDER_SELECTION);
            } elseif ($status === 'diproses') {
                $query->whereIn('workflow_status', [
                    \App\Models\ProcurementPackage::WORKFLOW_EXECUTION,
                    \App\Models\ProcurementPackage::WORKFLOW_PAYMENT_PROCESS
                ]);
            } elseif ($status === 'selesai') {
                $query->where('workflow_status', \App\Models\ProcurementPackage::WORKFLOW_COMPLETED);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('package', function ($q) use ($search) {
                $q->where('nama_paket', 'like', "%{$search}%")
                  ->orWhere('id_rup', 'like', "%{$search}%");
            });
        }

        $type = $request->input('type', 'penyedia'); // Default ke penyedia
        
        if ($type === 'dikecualikan') {
            $query->whereNotNull('dikecualikan_type');
        } elseif ($type === 'swakelola') {
            $query->whereNull('dikecualikan_type')
                  ->whereHas('package', function ($q) {
                      $q->where('jenis_pengadaan', 'like', '%swakelola%');
                  });
        } else {
            // Default: penyedia
            $query->whereNull('dikecualikan_type')
                  ->whereHas('package', function ($q) {
                      $q->where('jenis_pengadaan', 'not like', '%swakelola%');
                  });
        }

        $procurementPackages = $query->orderByDesc('id')->paginate(15)->withQueryString();

        $programs = \App\Models\Program::orderBy('nama')->get();
        $statuses = \App\Models\ProcurementPackage::getWorkflowStatuses();

        return view('procurement-packages.index', compact('procurementPackages', 'programs', 'statuses', 'type'));
    }

    public function store(Request $request, Package $package): RedirectResponse
    {
        abort_unless(
            $request->user()->hasAnyRole(['Admin', 'Kabid']),
            403,
            'Hanya Admin atau Kabid yang dapat membuat ruang pengadaan.'
        );

        $showRoute = $request->input('source') === 'kabid'
            ? 'kabid.procurement-packages.show'
            : 'admin.procurement-packages.show';
        if (!$package->isComplete()) {
            return back()->with('error', 'Hanya package yang sudah lengkap datanya yang dapat dieksekusi.');
        }

        $existing = $package->procurementPackage;

        if ($existing) {
            return redirect()
                ->route($showRoute, $package)
                ->with('warning', 'Paket Pengadaan untuk package ini sudah ada.');
        }

        $procurementPackage = ProcurementPackage::create([
            'package_id' => $package->id,
            'number' => null,
            'status' => 'draft',
            'created_by' => Auth::id(),
        ]);

        return redirect()
            ->route($showRoute, $package)
            ->with('success', 'Paket Pengadaan berhasil dibuat.');
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
            'jangka_waktu_nilai' => 'nullable|integer|min:1',
            'jangka_waktu_satuan' => 'nullable|string|in:hari,minggu,bulan,tahun',
            'tanggal_barang_diterima' => 'nullable|date',
            'ada_garansi' => 'nullable|boolean',    
            'garansi_nilai' => 'nullable|integer|min:1',
            'garansi_satuan' => 'nullable|string|in:hari,minggu,bulan,tahun',
            'layanan_purna_jual' => 'nullable|boolean',
            'tanggal_spesifikasi' => 'nullable|date',
            'items' => 'nullable|array',
            'items.*.nama_barang_jasa' => 'nullable|string|max:255',
            'items.*.spesifikasi' => 'nullable|string',
            'items.*.volume' => 'nullable|numeric|min:0',
            'items.*.satuan' => 'nullable|string|max:255',
            'items.*.harga_satuan_dpa' => 'nullable|string',
            'items.*.pdn' => 'nullable|boolean',
            'items.*.tkdn' => 'nullable|numeric|min:0|max:100',
            'items.*.kode_mak' => 'nullable|string|max:255',
        ]);

        if (isset($data['ada_garansi']) && !$data['ada_garansi']) {
            $data['garansi_nilai'] = null;
            $data['garansi_satuan'] = null;
        }

        $procurementPackage->update($data);

        $technicalSpecification = $procurementPackage->technicalSpecification;

        if (!$technicalSpecification) {
            $technicalSpecification = TechnicalSpecification::create([
                'procurement_package_id' => $procurementPackage->id,
            ]);
        }

        // Sync contract info to TechnicalSpecification
        $technicalSpecification->update([
            'tanggal' => $data['tanggal_spesifikasi'] ?? null,
        ]);

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

        if ($request->input('action') === 'generate_ai') {
            return app()->call([$this, 'generateDraft'], ['procurementPackage' => $procurementPackage]);
        }

        return back()->with('success', 'Informasi paket berhasil disimpan.');
    }

    public function updateDikecualikan(Request $request, ProcurementPackage $procurementPackage)
    {
        $data = $request->validate([
            'dikecualikan_type' => 'nullable|in:di_luar_sistem,di_dalam_sistem',
        ]);

        $procurementPackage->update($data);

        return back()->with('success', 'Data Dikecualikan berhasil disimpan.');
    }

    public function complete(Request $request, ProcurementPackage $procurementPackage)
    {
        $procurementPackage->update([
            'status' => 'complete',
            'workflow_status' => \App\Models\ProcurementPackage::WORKFLOW_COMPLETED
        ]);
        
        return back()->with('success', 'Paket berhasil diselesaikan dan data telah dikunci.');
    }

    public function updatePrompt(Request $request, ProcurementPackage $procurementPackage)
    {
        $request->validate([
            'prompt' => 'required|string',
        ]);

        \App\Models\AiPrompt::updateOrCreate(
            ['code' => 'technical_specification'],
            [
                'name' => 'Spesifikasi Teknis',
                'prompt' => $request->prompt,
                'is_active' => true,
            ]
        );

        return back()->with('success', 'Prompt AI berhasil diperbarui.');
    }

    public function generateDraft(ProcurementPackage $procurementPackage, OpenAIService $openai)
    {
        set_time_limit(120);

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

        try {
            $prompt = $this->buildTechnicalSpecificationPrompt($package, $technicalSpecification, $items);
            
            $draft = $openai->generateTechnicalSpecificationJson($prompt);

            $technicalSpecification->update([
                'latar_belakang' => $draft['latar_belakang'] ?? null,
                'maksud' => $draft['maksud'] ?? null,
                'target_sasaran' => $draft['target_sasaran'] ?? null,
                'uraian_pekerjaan' => $draft['uraian_pekerjaan'] ?? null,
            ]);

            return redirect()
                ->route('procurement-packages.technical-specifications.show',
                    $procurementPackage->package
                )
                ->with(
                    'success',
                    'Dokumen Spesifikasi Teknis berhasil dibuat.'
                );
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghasilkan dokumen. Server AI mungkin sedang sibuk. Detail: ' . $e->getMessage());
        }
    }

    private function buildTechnicalSpecificationPrompt(Package $package, \App\Models\TechnicalSpecification $technicalSpecification, array $items): string
    {
        $skpd = Skpd::first();
        $promptTemplate = AiPrompt::where(
            'code',
            'technical_specification'
        )->where(
            'is_active',
            true
        )->first();
        
        if (!$promptTemplate) {
            throw new \Exception('Template Prompt untuk Spesifikasi Teknis belum diatur atau tidak aktif.');
        }
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
                $skpd->nama,
                $package->nama_paket,
                $package->program?->nama,
                $package->activity?->nama,
                $package->subActivity?->nama,
                $package->jenis_pengadaan,
                json_encode($items, JSON_PRETTY_PRINT),
            ],
            $template
        );  

        // Informasi Garansi
        $garansiText = "Tidak ada garansi yang diperlukan untuk paket ini.";
        if (!empty($technicalSpecification->garansi_nilai)) {
            $garansiText = "Penyedia WAJIB memberikan garansi selama " . $technicalSpecification->garansi_nilai . " " . ucfirst($technicalSpecification->garansi_satuan) . ".";
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