<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Dari ponsel, pintu masuk aplikasi adalah percakapan — bukan dasbor.
 *
 * Dipasang hanya pada halaman pendaratan (beranda dan pengalih /dashboard).
 * Menu di dalam asisten menunjuk langsung ke dashboard.staf / dashboard.kabid
 * / dashboard.admin, jadi pengguna tetap bisa membuka dasbor penuh dari HP
 * tanpa terpental balik ke chat.
 */
class ArahkanPonselKeAsisten
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $this->dariPonsel($request)) {
            return redirect()->route('asisten');
        }

        return $next($request);
    }

    private function dariPonsel(Request $request): bool
    {
        return (bool) preg_match(
            '/Android|iPhone|iPod|Windows Phone|webOS|BlackBerry|Opera Mini|IEMobile/i',
            (string) $request->userAgent()
        );
    }
}
