<?php

use App\Models\Plan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plan_modules', function (Blueprint $t) {
            $t->string('description', 120)->nullable()->after('label');
            $t->boolean('is_system')->default(false)->after('icon'); // built-in: cannot be deleted
        });

        // Keep owner-created modules after the built-ins.
        DB::table('plan_modules')->increment('sort_order', 100);

        // Copy built-in modules from Plan::FEATURES so they can be renamed / switched off from the UI.
        // The key never changes: it is what the code checks with hasFeature().
        $now = now();
        $i = 1;
        foreach (Plan::FEATURES as $key => $f) {
            DB::table('plan_modules')->insertOrIgnore([
                'key' => $key,
                'label' => $f['label'],
                'icon' => $f['icon'],
                'is_system' => true,
                'is_active' => true,
                'sort_order' => $i++,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('plan_modules')->where('is_system', true)->delete();

        Schema::table('plan_modules', function (Blueprint $t) {
            $t->dropColumn(['description', 'is_system']);
        });
    }
};