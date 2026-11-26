<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};

return new class extends Migration {
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $t) {
            $t->id();
            $t->string('name', 60)->unique();
            $t->string('slug', 70)->unique();            // companies.plan stores this slug
            $t->string('description', 140)->nullable();
            $t->unsignedInteger('monthly_price');
            $t->unsignedInteger('yearly_price');
            $t->unsignedSmallInteger('max_users');
            $t->unsignedSmallInteger('max_branches');
            $t->json('features')->nullable();
            $t->boolean('is_popular')->default(false);
            $t->boolean('is_active')->default(true);     // inactive plans are hidden from "Add Company"
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();
        });

        $all = ['bilty', 'bookings', 'quotations', 'inventory', 'warehouse', 'payments', 'ai', 'whatsapp'];
        $seed = [
            ['Starter', 'starter', 'For small local movers', 999, 9990, 5, 1, ['bilty', 'bookings', 'quotations', 'payments'], false],
            ['Business', 'business', 'For growing multi-branch teams', 2499, 24990, 20, 5, array_slice($all, 0, 7), true],
            ['Enterprise', 'enterprise', 'Everything, with high limits', 5999, 59990, 100, 25, $all, false],
        ];
        foreach ($seed as $i => [$n, $s, $d, $m, $y, $u, $b, $f, $p]) {
            DB::table('plans')->insert(['name' => $n, 'slug' => $s, 'description' => $d, 'monthly_price' => $m, 'yearly_price' => $y,
                'max_users' => $u, 'max_branches' => $b, 'features' => json_encode($f), 'is_popular' => $p, 'sort_order' => $i + 1, 'created_at' => now(), 'updated_at' => now()]);
        }
    }
    public function down(): void { Schema::dropIfExists('plans'); }
};
