<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // Webhooks are called by Meta / WhatsApp, so they cannot send a CSRF token.
        $middleware->validateCsrfTokens(except: [
            'admin/meta/webhook',
            'admin/whatsapp/webhook',
        ]);

        // Company isolation: logs out blocked users and suspended companies on every request.
        $middleware->appendToGroup('web', \App\Http\Middleware\EnsureTenantIsActive::class);

        // IMPORTANT: ONE alias() call only. A second alias() call replaces the first one,
        // which is what caused "Target class [super_admin] does not exist".
        $middleware->alias([
            'super_admin'  => \App\Http\Middleware\SuperAdminMiddleware::class,
            'console.gate' => \App\Http\Middleware\ConsoleGate::class,
            'console.auth' => \App\Http\Middleware\ConsoleAuth::class,
        ]);

        $middleware->trustProxies(at: '*'); // ONLY if the origin accepts traffic from Cloudflare alone
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();