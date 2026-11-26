<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'plan_id',
        'status',
        'trial_ends_at',
        'subscription_ends_at',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'subscription_ends_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function companyConsole(): HasOne
    {
        return $this->hasOne(CompanyConsole::class, 'tenant_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'tenant_id');
    }

    // यदि आपका authentication Admin model से चलता है
    public function admins(): HasMany
    {
        return $this->hasMany(Admin::class, 'tenant_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'tenant_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(CompanyActivity::class, 'tenant_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Plan Limits
    |--------------------------------------------------------------------------
    */

    public function seatLimit(): ?int
    {
        return $this->plan?->max_users;
    }

    public function branchLimit(): ?int
    {
        return $this->plan?->max_branches;
    }

    public function seatsUsed(): int
    {
        return $this->admins()->count();
    }

    public function hasFreeSeat(): bool
    {
        $limit = $this->seatLimit();

        return $limit === null || $this->seatsUsed() < $limit;
    }

    public function hasFreeBranch(): bool
    {
        $limit = $this->branchLimit();

        $used = $this->companyConsole?->branches()->count() ?? 0;

        return $limit === null || $used < $limit;
    }

    /*
    |--------------------------------------------------------------------------
    | Feature Access
    |--------------------------------------------------------------------------
    */

    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->plan?->features ?? [], true);
    }

    /*
    |--------------------------------------------------------------------------
    | Subscription Status
    |--------------------------------------------------------------------------
    */

    public function isSuspended(): bool
    {
        return in_array($this->status, ['pending', 'suspended'], true);
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired'
            || ($this->status === 'trial' && $this->trial_ends_at?->isPast())
            || ($this->status === 'active' && $this->subscription_ends_at?->isPast());
    }

    public function isUsable(): bool
    {
        return !$this->isSuspended() && !$this->isExpired();
    }
}