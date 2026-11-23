<?php

namespace App\Models\Concerns;

use App\Models\Scopes\TenantScope;
use App\Models\Tenant;
use App\Support\Tenancy\CurrentTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function (Model $model) {
            $current = app(CurrentTenant::class)->id();

            if ($current === null) {
                if ($model->tenantScopeFailsClosed()) {
                    throw new RuntimeException(class_basename($model) . ': no tenant context, creation refused.');
                }
                return;
            }

            $given = $model->getAttribute('tenant_id');
            if ($given !== null && (int) $given !== $current) {
                throw new RuntimeException('Cross-tenant write blocked.');
            }

            $model->setAttribute('tenant_id', $current);   // never taken from the request
        });

        static::updating(function (Model $model) {
            if ($model->isDirty('tenant_id')) {
                throw new RuntimeException('tenant_id cannot be changed.');
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function tenantScopeFailsClosed(): bool
    {
        return true;
    }
}