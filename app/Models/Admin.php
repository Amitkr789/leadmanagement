<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable, BelongsToTenant;

    protected $table = 'admins';

    // tenant_id, google_id, avatar, google_linked_at, last_login_* are intentionally NOT fillable.
    // They are only set by the tenant trait or with forceFill() inside trusted services.
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'google_id',
    ];

    protected function casts(): array
    {
        return [
            'password'         => 'hashed',
            'google_linked_at' => 'datetime',
            'last_login_at'    => 'datetime',
        ];
    }

    /**
     * Admin is the only model that is NOT fail-closed: the session guard and the login
     * form must be able to load a user before the tenant is known.
     */
    public function tenantScopeFailsClosed(): bool
    {
        return false;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function hasPassword(): bool
    {
        return $this->password !== null && $this->password !== '';
    }
}