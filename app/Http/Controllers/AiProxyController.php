<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Jembatan KDMP → AI Service (aplikasi ai-kdmp).
 *
 * Browser hanya berbicara ke KDMP memakai session + CSRF; kunci internal AI
 * Service tidak pernah keluar dari server. Identitas pengguna juga diambil
 * dari sesi, bukan dari badan permintaan, supaya tidak bisa dipalsukan.
 */
class AiProxyController extends Controller
{
    public function chat(Request $request): JsonResponse
    {
        $data = $request->validate([
            'prompt' => ['required', 'string', 'max:5000'],
            'session_id' => ['nullable', 'string', 'max:64'],
            'context' => ['nullable', 'array'],
        ]);

        return $this->teruskan($request, '/api/v1/ai/chat', $data);
    }

    public function approve(Request $request): JsonResponse
    {
        $data = $request->validate([
            'job_id' => ['required', 'string', 'max:64'],
            'decision' => ['required', 'string', 'in:approve,reject,revise'],
            'revision_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        return $this->teruskan($request, '/api/v1/ai/approve', $data);
    }

    /**
     * Kirim permintaan ke AI Service dengan kunci internal, lalu kembalikan
     * jawabannya apa adanya ke pemanggil.
     */
    private function teruskan(Request $request, string $path, array $payload): JsonResponse
    {
        $secret = config('ai_service.secret');

        if (blank($secret)) {
            Log::warning('AI Service dipanggil tetapi AI_SERVICE_SECRET belum diisi.');

            return response()->json([
                'status' => 'error',
                'message' => 'AI Assistant belum dikonfigurasi. Hubungi administrator.',
                'error' => 'AI_SERVICE_NOT_CONFIGURED',
            ], 503);
        }

        // Identitas diambil dari sesi — bukan dari kiriman browser.
        $user = $request->user();
        $payload['user_id'] = (string) $user->id;
        $payload['user_name'] = $user->name;

        try {
            $response = Http::withHeaders(['X-KDMP-SECRET-KEY' => $secret])
                ->acceptJson()
                ->timeout((int) config('ai_service.timeout'))
                ->post(config('ai_service.base_url') . $path, $payload);
        } catch (ConnectionException $e) {
            Log::error('Gagal menghubungi AI Service: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'AI Service sedang tidak dapat dihubungi. Coba beberapa saat lagi.',
                'error' => 'AI_SERVICE_UNREACHABLE',
            ], 502);
        }

        if ($response->failed()) {
            Log::error('AI Service menolak permintaan', [
                'path' => $path,
                'status' => $response->status(),
            ]);
        }

        return response()->json(
            $response->json() ?? [
                'status' => 'error',
                'message' => 'Jawaban AI Service tidak dapat dibaca.',
                'error' => 'AI_SERVICE_BAD_RESPONSE',
            ],
            $response->status()
        );
    }
}
