<?php

namespace App\Http\Controllers;

use App\Services\AI\PenyiapDokumen;
use App\Services\AI\SpdDraftService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * Jembatan KDMP → AI Service (aplikasi ai-kdmp).
 *
 * Browser hanya berbicara ke KDMP memakai session + CSRF; kunci internal AI
 * Service tidak pernah keluar dari server. Identitas pengguna diambil dari
 * sesi. Isi draf (slot) tersimpan di AI Service dan hanya boleh diubah
 * lewat resolusi SpdDraftService — eksekusi tidak pernah memercayai data
 * kiriman browser.
 */
class AiProxyController extends Controller
{
    public function __construct(private SpdDraftService $draftService)
    {
    }

    /**
     * POST /ai/chat — pesan percakapan (perintah baru atau jawaban slot).
     */
    public function chat(Request $request): JsonResponse
    {
        $data = $request->validate([
            'prompt' => ['required', 'string', 'max:5000'],
            'session_id' => ['nullable', 'string', 'max:64'],
            'job_id' => ['nullable', 'string', 'max:64'],
        ]);

        if ($gagal = $this->pastikanSiap()) {
            return $gagal;
        }

        // Konteks untuk ekstraksi LLM: status slot (terisi/kosong), slot yang
        // ditunggu, dan kandidat paket {id, label}. Tanpa data pribadi —
        // pencocokan pegawai tetap terjadi di sini, bukan di penyedia LLM.
        $slotsSebelum = [];
        $konteks = ['kandidat_paket' => $this->draftService->kandidatUntukLlm()];

        if (! empty($data['job_id'])) {
            $job = $this->ambilJob($request, $data['job_id']);

            if (! $job instanceof JsonResponse) {
                $slotsSebelum = $job['payload']['slots'] ?? [];
                $konteks['slot_status'] = $this->draftService->statusRingkas($slotsSebelum);
                $konteks['menunggu'] = $job['payload']['menunggu'] ?? null;
            }
        }

        $respon = $this->kirim('post', '/api/v1/ai/chat', $request, [
            'prompt' => $data['prompt'],
            'session_id' => $data['session_id'] ?? null,
            'job_id' => $data['job_id'] ?? null,
            'context' => $konteks,
        ]);

        if ($respon instanceof JsonResponse) {
            return $respon;
        }

        $isi = $respon->json('data') ?? [];

        // ── Ekstraksi LLM: banyak slot terisi sekaligus ──────────
        if (($isi['mode'] ?? '') === 'ekstraksi') {
            return $this->terapkanEkstraksi($request, $isi, $slotsSebelum);
        }

        // ── Jawaban untuk slot yang sedang ditunggu (jalur regex) ─
        if (($isi['mode'] ?? '') === 'jawaban_slot') {
            return $this->terapkanJawabanSlot($request, $isi);
        }

        // ── Perintah baru berniat draf SPD ───────────────────────
        if (($isi['intent']['intent'] ?? '') === 'DOC_GEN_SPD' && ! empty($isi['job_id'])) {
            $slots = $this->draftService->resolveAwal($isi['intent']['entities'] ?? []);

            $hasil = $this->simpanSlots($request, $isi['job_id'], $slots);
            if ($hasil instanceof JsonResponse) {
                return $hasil;
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'session_id' => $isi['session_id'] ?? null,
                    'response_text' => trim(($isi['response_text'] ?? '') . "\n\n" . ($hasil['pesan'] ?? '')),
                    'draft' => $this->draftService->draftUntukWidget(
                        $isi['job_id'], $slots, (bool) ($hasil['lengkap'] ?? false), $hasil['pesan'] ?? null
                    ),
                ],
                'error' => null,
            ]);
        }

        // ── Selain draf SPD: teruskan apa adanya ─────────────────
        $teks = $isi['response_text'] ?? '';
        if (($isi['intent']['intent'] ?? '') === 'DOC_GEN_SURAT_TUGAS') {
            $teks .= "\n\n(Draf Surat Tugas lewat AI belum tersedia — sementara ini baru SPD.)";
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'session_id' => $isi['session_id'] ?? null,
                'response_text' => trim($teks),
                'draft' => null,
            ],
            'error' => null,
        ]);
    }

    /**
     * POST /ai/upload — foto atau PDF dokumen dibaca AI.
     *
     * PDF digital dibaca sendiri di server (gratis dan persis); foto serta
     * PDF hasil pindai dikirim ke model penglihatan. Berkasnya tidak pernah
     * disimpan: disiapkan di memori, dikirim, lalu dibuang. Yang tersimpan
     * hanya transkripnya di sisi AI Service.
     */
    public function unggah(Request $request, PenyiapDokumen $penyiap): JsonResponse
    {
        $request->validate([
            'berkas' => ['required', 'file', 'max:' . (int) (PenyiapDokumen::UKURAN_MAKSIMAL / 1024)],
            'job_id' => ['nullable', 'string', 'max:64'],
            'session_id' => ['nullable', 'string', 'max:64'],
        ], [
            'berkas.max' => 'Ukuran berkas melebihi 8 MB.',
        ]);

        if ($gagal = $this->pastikanSiap()) {
            return $gagal;
        }

        try {
            $lampiran = $penyiap->siapkan($request->file('berkas'));
        } catch (InvalidArgumentException $e) {
            return $this->gagal($e->getMessage(), 'BERKAS_TIDAK_DIDUKUNG', 422);
        }

        $jobId = $request->input('job_id');
        $slotsSebelum = [];
        $konteks = ['kandidat_paket' => $this->draftService->kandidatUntukLlm()];

        if ($jobId) {
            $job = $this->ambilJob($request, $jobId);

            if (! $job instanceof JsonResponse) {
                $slotsSebelum = $job['payload']['slots'] ?? [];
                $konteks['slot_status'] = $this->draftService->statusRingkas($slotsSebelum);
            }
        }

        $respon = $this->kirim('post', '/api/v1/ai/upload', $request, [
            'lampiran' => $lampiran,
            'session_id' => $request->input('session_id'),
            'job_id' => $jobId,
            'context' => $konteks,
        ]);

        if ($respon instanceof JsonResponse) {
            return $respon;
        }

        if ($respon->failed()) {
            return $this->gagal(
                $respon->json('message') ?? 'Dokumen tidak dapat dibaca.',
                $respon->json('error') ?? 'VISION_FAILED',
                $respon->status()
            );
        }

        $isi = $respon->json('data') ?? [];

        // Slot hasil bacaan tetap melewati resolusi & validasi yang sama
        // dengan yang diketik — dokumen bukan sumber yang lebih dipercaya.
        $slots = $this->draftService->terapkanEkstraksi(
            ($isi['job_baru'] ?? false) ? [] : $slotsSebelum,
            $isi['fields'] ?? []
        );

        $hasil = $this->simpanSlots($request, $isi['job_id'], $slots);
        if ($hasil instanceof JsonResponse) {
            return $hasil;
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'session_id' => $isi['session_id'] ?? null,
                'jenis_dokumen' => $isi['jenis_dokumen'] ?? null,
                'teks' => $isi['teks'] ?? '',
                'response_text' => trim(($isi['response_text'] ?? '') . "\n\n" . ($hasil['pesan'] ?? '')),
                'draft' => $this->draftService->draftUntukWidget(
                    $isi['job_id'], $slots, (bool) ($hasil['lengkap'] ?? false), $hasil['pesan'] ?? null
                ),
            ],
            'error' => null,
        ]);
    }

    /**
     * POST /ai/draft — pembaruan satu slot dari kartu draf.
     */
    public function draft(Request $request): JsonResponse
    {
        $data = $request->validate([
            'job_id' => ['required', 'string', 'max:64'],
            'slot' => ['required', 'string', 'max:40'],
            'nilai' => ['nullable'],
        ]);

        if ($gagal = $this->pastikanSiap()) {
            return $gagal;
        }

        $job = $this->ambilJob($request, $data['job_id']);
        if ($job instanceof JsonResponse) {
            return $job;
        }

        if (! in_array($job['status'], ['COLLECTING', 'AWAITING_APPROVAL'], true)) {
            return $this->gagal("Draf berstatus {$job['status']} dan tidak bisa diubah lagi.", 'DRAFT_CLOSED', 409);
        }

        $slots = $job['payload']['slots'] ?? [];

        try {
            $slots = $this->draftService->updateSlot($slots, $data['slot'], $data['nilai']);
        } catch (InvalidArgumentException $e) {
            return $this->gagal($e->getMessage(), 'SLOT_INVALID', 422);
        }

        $hasil = $this->simpanSlots($request, $data['job_id'], $slots);
        if ($hasil instanceof JsonResponse) {
            return $hasil;
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'draft' => $this->draftService->draftUntukWidget(
                    $data['job_id'], $slots, (bool) ($hasil['lengkap'] ?? false), $hasil['pesan'] ?? null
                ),
            ],
            'error' => null,
        ]);
    }

    /**
     * POST /ai/approve — persetujuan final. Hanya menerima job_id;
     * seluruh isi draf dibaca dari server, lalu SPD ditulis ke database.
     */
    public function approve(Request $request): JsonResponse
    {
        $data = $request->validate([
            'job_id' => ['required', 'string', 'max:64'],
        ]);

        if ($gagal = $this->pastikanSiap()) {
            return $gagal;
        }

        $job = $this->ambilJob($request, $data['job_id']);
        if ($job instanceof JsonResponse) {
            return $job;
        }

        if ($job['status'] !== 'AWAITING_APPROVAL') {
            return $this->gagal(
                "Draf berstatus {$job['status']} — belum siap disetujui. Lengkapi dulu datanya.",
                'DRAFT_NOT_READY', 409
            );
        }

        $slots = $job['payload']['slots'] ?? [];

        // Validasi penuh terhadap database SEBELUM gerbang persetujuan
        // ditutup, supaya kegagalan tidak meninggalkan job menggantung.
        try {
            $hasilEksekusi = $this->draftService->eksekusi($slots, (int) $request->user()->id);
        } catch (InvalidArgumentException $e) {
            return $this->gagal($e->getMessage(), 'DRAFT_INVALID', 422);
        }

        $travelOrder = $hasilEksekusi['travel_order'];
        $package = $hasilEksekusi['package'];

        // Catat persetujuan + tutup job di AI Service (audit trail).
        $this->kirim('post', '/api/v1/ai/approve', $request, [
            'job_id' => $data['job_id'],
            'decision' => 'approve',
        ]);

        $this->kirim('post', "/api/v1/ai/job/{$data['job_id']}/slots", $request, [
            'slots' => $slots,
            'status' => 'EXECUTED',
            'output_result' => [
                'travel_order_id' => $travelOrder->id,
                'package_id' => $package->id,
            ],
        ]);

        $prefix = $this->rolePrefix($request);

        return response()->json([
            'status' => 'success',
            'data' => [
                'message' => 'Draf SPD tersimpan sebagai pengajuan berstatus draft — silakan periksa rinciannya lalu ajukan seperti biasa.',
                'travel_order_id' => $travelOrder->id,
                'redirect_url' => route($prefix . '.packages.travel-orders.show', [$package, $travelOrder]),
            ],
            'error' => null,
        ]);
    }

    // ── Alur pembantu ─────────────────────────────────────────────

    /** Mode 'ekstraksi': terapkan seluruh field LLM ke slot draf. */
    private function terapkanEkstraksi(Request $request, array $isi, array $slotsSebelum): JsonResponse
    {
        $jobId = $isi['job_id'];

        // Draf baru dimulai dari kerangka kosong; draf lanjutan dari slot
        // tersimpan (kiriman AI service hanya fields, bukan slot).
        $slots = ($isi['job_baru'] ?? false) ? [] : $slotsSebelum;

        $slots = $this->draftService->terapkanEkstraksi($slots, $isi['fields'] ?? []);

        $hasil = $this->simpanSlots($request, $jobId, $slots);
        if ($hasil instanceof JsonResponse) {
            return $hasil;
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'session_id' => $isi['session_id'] ?? null,
                'response_text' => trim(($isi['response_text'] ?? '') . "\n\n" . ($hasil['pesan'] ?? '')),
                'draft' => $this->draftService->draftUntukWidget(
                    $jobId, $slots, (bool) ($hasil['lengkap'] ?? false), $hasil['pesan'] ?? null
                ),
            ],
            'error' => null,
        ]);
    }

    private function terapkanJawabanSlot(Request $request, array $isi): JsonResponse
    {
        $jobId = $isi['job_id'];

        $job = $this->ambilJob($request, $jobId);
        if ($job instanceof JsonResponse) {
            return $job;
        }

        $slots = $job['payload']['slots'] ?? [];

        try {
            $slots = $this->draftService->jawabSlot($slots, $isi['jawaban_untuk'], $isi['teks']);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'response_text' => $e->getMessage(),
                    'draft' => $this->draftService->draftUntukWidget($jobId, $slots, false, null),
                ],
                'error' => null,
            ]);
        }

        $hasil = $this->simpanSlots($request, $jobId, $slots);
        if ($hasil instanceof JsonResponse) {
            return $hasil;
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'response_text' => $hasil['pesan'] ?? 'Baik, sudah saya catat.',
                'draft' => $this->draftService->draftUntukWidget(
                    $jobId, $slots, (bool) ($hasil['lengkap'] ?? false), $hasil['pesan'] ?? null
                ),
            ],
            'error' => null,
        ]);
    }

    /** @return array|JsonResponse data job dari AI Service */
    private function ambilJob(Request $request, string $jobId): array|JsonResponse
    {
        $respon = $this->kirim('get', "/api/v1/ai/job/{$jobId}", $request);

        if ($respon instanceof JsonResponse) {
            return $respon;
        }

        if ($respon->status() === 404) {
            return $this->gagal('Draf tidak ditemukan atau bukan milik Anda.', 'JOB_NOT_FOUND', 404);
        }

        return $respon->json('data') ?? [];
    }

    /** @return array|JsonResponse hasil patch slot {lengkap, pesan, status} */
    private function simpanSlots(Request $request, string $jobId, array $slots): array|JsonResponse
    {
        $respon = $this->kirim('post', "/api/v1/ai/job/{$jobId}/slots", $request, ['slots' => $slots]);

        if ($respon instanceof JsonResponse) {
            return $respon;
        }

        if ($respon->failed()) {
            return $this->gagal(
                $respon->json('message') ?? 'AI Service menolak pembaruan draf.',
                $respon->json('error') ?? 'AI_SERVICE_REJECTED',
                $respon->status()
            );
        }

        return $respon->json('data') ?? [];
    }

    /**
     * Panggilan server-to-server ke AI Service. Identitas pengguna SELALU
     * dari sesi — kiriman browser tidak pernah diteruskan sebagai identitas.
     */
    private function kirim(string $method, string $path, Request $request, array $payload = []): ClientResponse|JsonResponse
    {
        $user = $request->user();
        $identitas = ['user_id' => (string) $user->id, 'user_name' => $user->name];

        try {
            $pending = Http::withHeaders(['X-KDMP-SECRET-KEY' => config('ai_service.secret')])
                ->acceptJson()
                ->timeout((int) config('ai_service.timeout'));

            $url = config('ai_service.base_url') . $path;

            return $method === 'get'
                ? $pending->get($url, $identitas)
                : $pending->post($url, array_merge($payload, $identitas));
        } catch (ConnectionException $e) {
            Log::error('Gagal menghubungi AI Service: ' . $e->getMessage());

            return $this->gagal(
                'AI Service sedang tidak dapat dihubungi. Coba beberapa saat lagi.',
                'AI_SERVICE_UNREACHABLE', 502
            );
        }
    }

    private function pastikanSiap(): ?JsonResponse
    {
        if (blank(config('ai_service.secret'))) {
            Log::warning('AI Service dipanggil tetapi AI_SERVICE_SECRET belum diisi.');

            return $this->gagal('AI Assistant belum dikonfigurasi. Hubungi administrator.', 'AI_SERVICE_NOT_CONFIGURED', 503);
        }

        return null;
    }

    private function rolePrefix(Request $request): string
    {
        return match (true) {
            $request->user()->hasRole('Kabid') => 'kabid',
            $request->user()->hasRole('Staff') => 'staf',
            default => 'admin',
        };
    }

    private function gagal(string $pesan, string $kode, int $status): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $pesan,
            'error' => $kode,
        ], $status);
    }
}
