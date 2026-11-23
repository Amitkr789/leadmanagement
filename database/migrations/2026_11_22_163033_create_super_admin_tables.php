<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Owner accounts live in their own table, fully separate from company users.
        Schema::create('super_admins', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->unique();
            $t->string('password');
            $t->text('two_factor_secret')->nullable();            // encrypted via model cast
            $t->unsignedInteger('two_factor_last_ts')->nullable(); // blocks code replay
            $t->timestamp('two_factor_confirmed_at')->nullable();
            $t->boolean('is_active')->default(true);
            $t->unsignedTinyInteger('failed_attempts')->default(0);
            $t->timestamp('locked_until')->nullable();
            $t->timestamp('last_login_at')->nullable();
            $t->string('last_login_ip', 45)->nullable();
            $t->timestamps();
        });

        // Audit trail of every sign-in event.
        Schema::create('super_admin_login_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('super_admin_id')->nullable()->constrained('super_admins')->nullOnDelete();
            $t->string('email')->nullable();
            $t->string('event', 20)->index(); // failed, locked, 2fa_failed, success, logout
            $t->string('ip', 45)->nullable();
            $t->string('user_agent', 255)->nullable();
            $t->timestamp('created_at')->useCurrent();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('super_admin_login_logs');
        Schema::dropIfExists('super_admins');
    }
};
