<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Free plans + free-trial length, decided per plan by the owner.
        Schema::table('plans', function (Blueprint $t) {
            $t->boolean('is_free')->default(false)->after('is_active');
            $t->unsignedSmallInteger('trial_days')->default(0)->after('is_free'); // 0 = no trial
        });

        // Modules the owner creates from the UI (built-ins stay in Plan::FEATURES).
        Schema::create('plan_modules', function (Blueprint $t) {
            $t->id();
            $t->string('key', 50)->unique();      // stored inside plans.features[]
            $t->string('label', 40);
            $t->string('icon', 40)->default('bi-puzzle');
            $t->boolean('is_active')->default(true);
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_modules');

        Schema::table('plans', function (Blueprint $t) {
            $t->dropColumn(['is_free', 'trial_days']);
        });
    }
};