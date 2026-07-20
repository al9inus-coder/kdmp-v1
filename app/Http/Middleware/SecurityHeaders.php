<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Report-Only dipakai sampai inline script/style lama dimigrasikan ke nonce.
        $response->headers->set(
            'Content-Security-Policy-Report-Only',
            "default-src 'self'; base-uri 'self'; object-src 'none'; frame-ancestors 'self'; "
            . "form-action 'self'; img-src 'self' data: https:; style-src 'self' 'unsafe-inline' https:; "
            . "script-src 'self' 'unsafe-inline' https:; connect-src 'self' https:"
        );

        if ($request->isSecure() || app()->environment('production')) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        return $response;
    }
}
