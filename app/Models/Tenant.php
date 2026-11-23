<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One tenant = one customer company with its own private CRM workspace.
 * NOTE: this is NOT the lead-directory "Company" model.
 * If your Console already has a table for companies, point $table (and the FKs) to it.
 */
class Tenant extends Model
{
    // protected $table = 'tenants';

    protected $fillable = ['name', 'status', 'trial_ends_at', 'max_users'];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
        ];
    }

    public function admins(): HasMany
    {
        return $this->hasMany(Admin::class);
    }

    /** Users allowed by the purchased plan. null = unlimited (also when the column does not exist yet). */
    public function seatLimit(): ?int
    {
        return $this->max_users === null ? null : (int) $this->max_users;
    }

    public function seatsUsed(): int
    {
        return Admin::withoutGlobalScopes()->where('tenant_id', $this->id)->count();
    }

    public function hasFreeSeat(): bool
    {
        $limit = $this->seatLimit();

        return $limit === null || $this->seatsUsed() < $limit;
    }

    /** Can people of this company sign in right now? */
    public function isUsable(): bool
    {
        if ($this->status === 'active') {
            return true;
        }

        return $this->status === 'trial'
            && ($this->trial_ends_at === null || $this->trial_ends_at->isFuture());
    }
}