<?php

namespace App\Providers;

use App\Support\Tenancy\CurrentTenant;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // scoped = a fresh instance per request / job (safe with Octane and queue workers)
        $this->app->scoped(CurrentTenant::class);
    }

    public function boot(): void
    {
        // Whenever the admin guard resolves a user, pin the whole request to that user's company.
        // This runs inside auth:admin, before route-model binding, so {task}, {lead}, ... are already tenant-scoped.
        Event::listen([Authenticated::class, Login::class], function ($event) {
            if ($event->guard === 'admin') {
                app(CurrentTenant::class)->set(
                    $event->user->tenant_id ? (int) $event->user->tenant_id : null
                );
            }
        });
    }
}