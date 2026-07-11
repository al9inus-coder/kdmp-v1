<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Percayai reverse proxy internal (Cloudflare tunnel) agar Laravel
        // membaca X-Forwarded-Proto/For dengan benar (https & IP asli klien).
        $middleware->trustProxies(at: '10.0.0.0/8');

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Spatie\Permission\Exceptions\UnauthorizedException $e, \Illuminate\Http\Request $request) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        });
        
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e, \Illuminate\Http\Request $request) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk melakukan tindakan tersebut.');
        });
    })->create();
