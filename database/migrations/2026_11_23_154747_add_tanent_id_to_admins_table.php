<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->foreignId('tenant_id')
                ->nullable()
                ->after('id')
                ->constrained('tenants')
                ->cascadeOnDelete();

            $table->string('google_id')->nullable()->unique()->after('email');
            $table->string('avatar')->nullable();
            $table->timestamp('google_linked_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();

            $table->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropUnique(['google_id']);

            $table->dropColumn([
                'tenant_id',
                'google_id',
                'avatar',
                'google_linked_at',
                'last_login_at',
                'last_login_ip',
            ]);

            $table->string('password')->nullable(false)->change();
        });
    }
};