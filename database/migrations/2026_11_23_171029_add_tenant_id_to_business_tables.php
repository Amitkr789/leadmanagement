<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Every table that stores one company's business data.
     * Tables that do not exist in your database are skipped, so the list can stay long.
     * Run `SHOW TABLES;` and add any missing name here before migrating.
     */
    private array $tables = [
        // lead directory (these three models already use BelongsToTenant)
        'leads', 'contacts', 'companies',
        // lead children
        'lead_reminders', 'lead_attachments',
        // other modules
        'deals',
        'tasks', 'task_checklist_items', 'task_comments', 'task_attachments',
        'bills', 'banks', 'billing_headers',
        'inventory', 'products', 'product_images', 'dealers',
        'meta_leads', 'knowledge_bases', 'kb_categories', 'kb_media',
        'chatbot_settings', 'chats', 'chat_messages', 'chat_notes',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table) || Schema::hasColumn($table, 'tenant_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $t) {
                // Nullable on purpose: old rows are filled by `php artisan tenancy:backfill`.
                $t->foreignId('tenant_id')->nullable()->after('id')
                  ->constrained('tenants')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'tenant_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropConstrainedForeignId('tenant_id');
                });
            }
        }
    }
};