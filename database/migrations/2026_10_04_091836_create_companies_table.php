<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('company_consoles', function (Blueprint $t) {
            $t->id();

            // Tenant relation: one workspace per tenant
            $t->foreignId('tenant_id')
                ->unique()
                ->constrained('tenants')
                ->cascadeOnDelete();

            // Company identity
            $t->string('name');
            $t->string('slug')->unique();

            // Contact information
            $t->string('contact_name');
            $t->string('mobile', 15);
            $t->string('email');
            $t->string('city', 80);
            $t->string('state', 80);
            $t->string('gst_number', 15)->nullable();
            $t->string('address', 300)->nullable();

            // Creator
            $t->foreignId('created_by')
                ->nullable()
                ->constrained('super_admins')
                ->nullOnDelete();

            $t->timestamps();
        });

        // Existing users table: keep company_id if other module
    }

    public function down(): void
    {
        

        Schema::dropIfExists('company_consoles');
    }
};