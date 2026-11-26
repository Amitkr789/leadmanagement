<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {

            // Connect Tenant with Plan
            $table->foreignId('plan_id')
                ->nullable()
                ->after('name')
                ->constrained('plans')
                ->nullOnDelete();

            // Subscription
            $table->string('billing_cycle', 20)
                ->nullable()
                ->after('status'); // monthly | yearly

            $table->timestamp('subscription_ends_at')
                ->nullable()
                ->after('trial_ends_at');

        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {

            $table->dropConstrainedForeignId('plan_id');

            $table->dropColumn([
                'billing_cycle',
                'subscription_ends_at',
            ]);

        });
    }
};