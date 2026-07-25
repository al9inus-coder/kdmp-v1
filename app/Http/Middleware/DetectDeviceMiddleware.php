<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class DetectDeviceMiddleware
{
    /**
     * Handle an incoming request and detect if it's a mobile device User-Agent.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userAgent = strtolower($request->header('User-Agent', ''));

        $isMobile = (bool) preg_match(
            '/(android|bb\d+|meego).+mobile|blackberry|iphone|ipad|ipod|opera mini|iemobile|mobile/i',
            $userAgent
        );

        // Share globally with all Blade views
        View::share('isMobileDevice', $isMobile);

        return $next($request);
    }
}
