<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One-time helper for an EXISTING single-company installation:
 *   php artisan tenancy:backfill --name="My Company"
 *   php artisan tenancy:backfill --tenant-id=1
 *
 * 1. creates (or reuses) one active tenant
 * 2. assigns every admin that has no tenant to it
 * 3. fills tenant_id on every business table row that has none
 *
 * It uses DB::table() on purpose, so the tenant scope does not get in the way.
 * If you already run several companies on this database, do NOT use it blindly:
 * assign admins to their own tenants by hand first.
 */
class TenancyBackfill extends Command
{
    protected $signature = 'tenancy:backfill {--name=My Company} {--tenant-id=}';
    protected $description = 'Assign all existing admins and business rows to one tenant';

    // Keep this list in sync with the tenant_id migration.
    private array $tables = [
        'leads', 'lead_reminders', 'lead_attachments', 'contacts', 'companies', 'deals',
        'tasks', 'task_checklist_items', 'task_comments', 'task_attachments',
        'bills', 'banks', 'billing_headers',
        'inventory', 'products', 'product_images', 'dealers',
        'meta_leads', 'knowledge_bases', 'kb_categories', 'kb_media',
        'chatbot_settings', 'chats', 'chat_messages', 'chat_notes',
    ];

    public function handle(): int
    {
        $tenant = $this->option('tenant-id')
            ? Tenant::findOrFail($this->option('tenant-id'))
            : Tenant::firstOrCreate(['name' => $this->option('name')], ['status' => 'active']);

        if ($tenant->status !== 'active') {
            $tenant->update(['status' => 'active', 'trial_ends_at' => null]);
        }

        DB::transaction(function () use ($tenant) {
            $admins = DB::table('admins')->whereNull('tenant_id')->update(['tenant_id' => $tenant->id]);
            $this->info("Admins assigned: {$admins}");

            foreach ($this->tables as $table) {
                if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'tenant_id')) {
                    continue;
                }

                $rows = DB::table($table)->whereNull('tenant_id')->update(['tenant_id' => $tenant->id]);
                $this->line("{$table}: {$rows} row(s)");
            }
        });

        $this->info("Done. Tenant #{$tenant->id} ({$tenant->name}) is active.");

        return self::SUCCESS;
    }
}