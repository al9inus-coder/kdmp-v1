<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AiPrompt;
use App\Models\Package;
use App\Models\Skpd;
use App\Models\TravelOrder;
use App\Services\AI\OpenAIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Laporan perjalanan dinas: generate narasi via AI (Gemini) + simpan hasil editan.
 */
class TravelReportController extends Controller
{
    private const FIELDS = [
        'nomor_surat_tugas' => 'nullable|string|max:255',
        'tanggal_surat_tugas' => 'nullable|date',
        'nomor_spd' => 'nullable|string|max:255',
        'tanggal_spd' => 'nullable|date',
        'prompt_latar_belakang' => 'nullable|string|max:5000',
        'prompt_kegiatan' => 'nullable|string|max:5000',
        'prompt_hasil' => 'nullable|string|max:5000',
        'prompt_kesimpulan' => 'nullable|string|max:5000',
        'prompt_penutup' => 'nullable|string|max:5000',
        'hasil_latar_belakang' => 'nullable|string|max:20000',
        'hasil_kegiatan' => 'nullable|string|max:20000',
        'hasil_dicapai' => 'nullable|string|max:20000',
        'hasil_kesimpulan' => 'nullable|string|max:20000',
        'hasil_penutup' => 'nullable|string|max:20000',
    ];

    public function store(Request $request, Package $package, TravelOrder $travelOrder): RedirectResponse
    {
        Gate::authorize('view', $package);
        abort_if((int) $travelOrder->package_id !== (int) $package->id, 404);

        $validated = $request->validate(self::FIELDS);

        $travelOrder->report()->updateOrCreate([], $validated);

        return redirect()
            ->route('staf.packages.travel-orders.show', [$package, $travelOrder])
            ->with('success', 'Laporan perjalanan dinas tersimpan.');
    }

    public function generate(Request $request, Package $package, TravelOrder $travelOrder, OpenAIService $openai): JsonResponse
    {
        Gate::authorize('view', $package);
        abort_if((int) $travelOrder->package_id !== (int) $package->id, 404);

        $validated = $request->validate(array_merge(self::FIELDS, [
            'prompt_kegiatan' => 'required|string|max:5000',
            'prompt_hasil' => 'required|string|max:5000',
        ]), [
            'prompt_kegiatan.required' => 'Isi poin kegiatan yang dilaksanakan — AI tidak boleh mengarang kegiatan di lapangan.',
            'prompt_hasil.required' => 'Isi poin hasil yang dicapai.',
        ]);

        $travelOrder->load('personnels.employee');

        try {
            $hasil = $openai->generateJson(
                $this->systemPrompt(),
                $this->buildPrompt($travelOrder, $validated)
            );
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Gagal generate laporan dari AI. Coba lagi beberapa saat.',
            ], 502);
        }

        $sections = [
            'hasil_latar_belakang' => (string) ($hasil['latar_belakang'] ?? ''),
            'hasil_kegiatan' => (string) ($hasil['kegiatan'] ?? ''),
            'hasil_dicapai' => (string) ($hasil['hasil_dicapai'] ?? ''),
            'hasil_kesimpulan' => (string) ($hasil['kesimpulan'] ?? ''),
            'hasil_penutup' => (string) ($hasil['penutup'] ?? ''),
        ];

        // Simpan input + hasil supaya tidak hilang meski user lupa klik Save.
        $travelOrder->report()->updateOrCreate([], array_merge(
            collect($validated)->except(array_keys($sections))->all(),
            $sections,
            ['generated_at' => now()],
        ));

        return response()->json(['hasil' => $sections]);
    }

    /**
     * System prompt: template aktif dari tabel ai_prompts (bisa diedit user),
     * fallback ke default di config/ai_prompts.php.
     */
    private function systemPrompt(): string
    {
        return AiPrompt::where('code', 'travel_report')
            ->where('is_active', true)
            ->value('prompt')
            ?: config('ai_prompts.travel_report');
    }

    public function updatePrompt(Request $request, Package $package, TravelOrder $travelOrder): RedirectResponse
    {
        Gate::authorize('view', $package);
        abort_if((int) $travelOrder->package_id !== (int) $package->id, 404);

        $request->validate(['prompt' => 'required|string|max:20000']);

        AiPrompt::updateOrCreate(
            ['code' => 'travel_report'],
            [
                'name' => 'Laporan Perjalanan Dinas',
                'prompt' => $request->prompt,
                'is_active' => true,
            ]
        );

        return back()->with('success', 'Prompt AI laporan berhasil diperbarui.');
    }

    private function buildPrompt(TravelOrder $travelOrder, array $input): string
    {
        $skpd = Skpd::first();
        $days = $travelOrder->tanggal_berangkat->diffInDays($travelOrder->tanggal_kembali) + 1;

        $pelaksana = $travelOrder->personnels
            ->map(fn ($p) => sprintf(
                '- %s (%s, Gol. %s)',
                $p->employee?->nama ?? 'Pegawai',
                $p->employee?->jabatan ?? '-',
                $p->employee?->golongan ?? '-'
            ))
            ->implode("\n");

        $konteks = collect([
            'Instansi: ' . ($skpd->nama ?? '-'),
            'Tujuan perjalanan: ' . $travelOrder->tempat_tujuan,
            'Tipe perjalanan: ' . $travelOrder->tipe_perjalanan,
            'Tanggal: ' . $travelOrder->tanggal_berangkat->locale('id')->translatedFormat('d F Y')
                . ' s.d. ' . $travelOrder->tanggal_kembali->locale('id')->translatedFormat('d F Y')
                . ' (' . $days . ' hari)',
            'Maksud perjalanan dinas: ' . $travelOrder->maksud_perjalanan,
            'Dasar pelaksanaan: ' . ($travelOrder->dasar_pelaksanaan ?: '-'),
            'Nomor Surat Tugas: ' . ($input['nomor_surat_tugas'] ?: '-'),
            'Nomor SPD: ' . ($input['nomor_spd'] ?: '-'),
            "Pelaksana:\n" . $pelaksana,
        ])->implode("\n");

        $poin = collect([
            'latar belakang' => $input['prompt_latar_belakang'] ?? null,
            'kegiatan yang dilaksanakan' => $input['prompt_kegiatan'] ?? null,
            'hasil yang dicapai' => $input['prompt_hasil'] ?? null,
            'kesimpulan dan saran' => $input['prompt_kesimpulan'] ?? null,
            'penutup' => $input['prompt_penutup'] ?? null,
        ])->filter()->map(fn ($v, $k) => strtoupper($k) . ":\n" . $v)->implode("\n\n");

        return <<<PROMPT
Susun narasi laporan perjalanan dinas berdasarkan data berikut:

=== DATA PERJALANAN ===
{$konteks}

=== POIN-POIN DARI PELAKSANA ===
{$poin}

Balas HANYA dengan JSON valid (tanpa markdown, tanpa teks lain) dengan struktur persis:
{"latar_belakang": "...", "kegiatan": "...", "hasil_dicapai": "...", "kesimpulan": "...", "penutup": "..."}
PROMPT;
    }
}
