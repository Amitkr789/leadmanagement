<?php

namespace App\Services\Tenancy;

use App\Models\Admin;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * The ONE place that creates a company. If the software owner's Console already has its own
 * companies table, change createTenant() here and nothing else needs to change.
 */
class TenantProvisioner
{
    /** Create a trial company and its first user (the owner, role = super_admin) in one transaction. */
    public function registerOwner(string $name, string $email, string $googleId, ?string $avatar, ?string $ip): Admin
    {
        return DB::transaction(function () use ($name, $email, $googleId, $avatar, $ip) {
            $tenant = $this->createTenant($name, $email);

            // forceCreate: tenant_id / google_id / role are not mass-assignable on purpose.
            $admin = Admin::forceCreate([
                'tenant_id'        => $tenant->id,
                'name'             => $name !== '' ? $name : Str::before($email, '@'),
                'email'            => $email,
                'password'         => null,          // Google-only until the owner sets a password
                'role'             => 'super_admin',
                'status'           => 'active',
                'google_id'        => $googleId,
                'avatar'           => $avatar,
                'google_linked_at' => now(),
                'last_login_at'    => now(),
                'last_login_ip'    => $ip,
            ]);

            Log::info('New company registered via Google', ['tenant_id' => $tenant->id, 'admin_id' => $admin->id]);

            return $admin;
        });
    }

    private function createTenant(string $name, string $email): Tenant
    {
        $base = $name !== '' ? $name : Str::before($email, '@');

        $attributes = [
            'name'          => Str::limit($base, 80, '') . "'s Workspace",   // the owner renames it on the onboarding page
            'status'        => 'trial',
            'trial_ends_at' => now()->addDays((int) config('tenancy.trial_days', 14)),
        ];

        if (Schema::hasColumn('tenants', 'max_users')) {
            $attributes['max_users'] = config('tenancy.trial_max_users');
        }

        return Tenant::create($attributes);
    }
}