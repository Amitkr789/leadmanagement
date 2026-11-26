<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $guarded = [];

    /** Built-in modules (code checks these keys). Owner-created modules live in plan_modules. */
    public const FEATURES = [
        'bilty' => ['label' => 'Bilty', 'icon' => 'bi-receipt'], 'bookings' => ['label' => 'Bookings', 'icon' => 'bi-calendar-check'],
        'quotations' => ['label' => 'Quotations', 'icon' => 'bi-file-earmark-text'], 'inventory' => ['label' => 'Inventory', 'icon' => 'bi-boxes'],
        'warehouse' => ['label' => 'Warehouse', 'icon' => 'bi-building'], 'payments' => ['label' => 'Payments', 'icon' => 'bi-wallet2'],
        'ai' => ['label' => 'AI Assistant', 'icon' => 'bi-stars'], 'whatsapp' => ['label' => 'WhatsApp', 'icon' => 'bi-whatsapp'],
        // Fleet: the Drivers, Vehicles, Assignments and Document Expiry pages
        'fleet' => ['label' => 'Drivers & Vehicles', 'icon' => 'bi-truck'],
        // Cashfree add-ons. Without them a company still enters the same details by hand.
        'dl_verification' => ['label' => 'Driving licence verification', 'icon' => 'bi-person-badge'],
        'vehicle_verification' => ['label' => 'Vehicle RC & PUC verification', 'icon' => 'bi-patch-check'],
        // WhatsApp Business (WABA) inbox: chat, share files / photos / videos / links, one-click lead. Head office only.
        'whatsapp_chat' => ['label' => 'WhatsApp Chat (WABA)', 'icon' => 'bi-chat-dots'],
    ];

    /**
     * Every module a plan can include, read from the plan_modules table.
     * $withInactive = false: modules offered when building a plan (active only).
     * $withInactive = true : every module, for showing labels and validating saved plans.
     *
     * @return array<string, array{label:string, icon:string, description:?string, custom:bool, system:bool, active:bool, id:?int}>
     */
    public static function featureMap(bool $withInactive = false): array
    {
        $rows = PlanModule::orderBy('sort_order')->orderBy('id')->get();
        $map = [];

        // Table not seeded yet: fall back to the built-in list.
        if ($rows->where('is_system', true)->isEmpty()) {
            foreach (self::FEATURES as $key => $f) {
                $map[$key] = $f + ['description' => null, 'custom' => false, 'system' => true, 'active' => true, 'id' => null];
            }
        }

        foreach ($rows as $m) {
            if (! $withInactive && ! $m->is_active) {
                continue;
            }

            $map[$m->key] = [
                'label' => $m->label,
                'icon' => $m->icon,
                'description' => $m->description,
                'custom' => ! $m->is_system,
                'system' => (bool) $m->is_system,
                'active' => (bool) $m->is_active,
                'id' => $m->id,
            ];
        }

        return $map;
    }

    protected function casts(): array
    {
        return [
            'features'   => 'array',
            'is_popular' => 'boolean',
            'is_active'  => 'boolean',
            'is_free'    => 'boolean',
            'trial_days' => 'integer',
        ];
    }

    public function scopeActive($q) { return $q->where('is_active', true); }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'plan_id');
    }

    public function hasTrial(): bool
    {
        return ! $this->is_free && $this->trial_days > 0;
    }

    /** Months the customer does not pay for on yearly billing, derived from the real prices. */
    public function getYearlySavingsMonthsAttribute(): float
    {
        if ($this->is_free || $this->monthly_price <= 0) {
            return 0.0;
        }

        return max(0.0, round(12 - $this->yearly_price / $this->monthly_price, 1));
    }

    /**
     * Tenant columns for a company that just picked this plan.
     * Usage: Tenant::create(['name' => $name, 'plan_id' => $plan->id] + $plan->startingState());
     */
    public function startingState(): array
    {
        if ($this->is_free) {
            return ['status' => 'active', 'trial_ends_at' => null, 'subscription_ends_at' => null];
        }

        if ($this->trial_days > 0) {
            return ['status' => 'trial', 'trial_ends_at' => now()->addDays($this->trial_days), 'subscription_ends_at' => null];
        }

        return ['status' => 'pending', 'trial_ends_at' => null, 'subscription_ends_at' => null];
    }
}