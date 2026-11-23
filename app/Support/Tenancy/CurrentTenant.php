<?php

namespace App\Support\Tenancy;

use Closure;

class CurrentTenant
{
    private ?int $id = null;

    public function set(?int $id): void { $this->id = $id; }
    public function id(): ?int          { return $this->id; }
    public function check(): bool       { return $this->id !== null; }

    /** Run code inside one tenant (webhooks, jobs, artisan) and restore the previous state. */
    public function run(int $tenantId, Closure $callback): mixed
    {
        $previous = $this->id;
        $this->id = $tenantId;

        try {
            return $callback();
        } finally {
            $this->id = $previous;
        }
    }
}