<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyConsole extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class, 'company_console_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'company_console_id');
    }

    // Workspace के plan को Tenant से प्राप्त करें
    public function getPlanAttribute()
    {
        return $this->tenant?->plan;
    }
}