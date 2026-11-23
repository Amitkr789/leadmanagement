<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deals', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->decimal('amount', 14, 2)->default(0);
            $t->string('stage', 20)->default('new')->index();
            $t->unsignedTinyInteger('probability')->default(10);
            $t->string('priority', 10)->default('medium');
            $t->string('deal_type', 30)->nullable();
            $t->string('source', 50)->nullable();
            $t->date('close_date')->nullable()->index();
            $t->timestamp('closed_at')->nullable();
            $t->string('lost_reason')->nullable();
            $t->text('description')->nullable();

            $t->foreignId('owner_id')->nullable()->constrained('admins')->nullOnDelete();
            $t->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $t->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $t->foreignId('created_by')->nullable()->constrained('admins')->nullOnDelete();
            $t->timestamps();
        });

        // A deal can have several contacts (decision maker, accountant, ...).
        Schema::create('deal_contact', function (Blueprint $t) {
            $t->id();
            $t->foreignId('deal_id')->constrained()->cascadeOnDelete();
            $t->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $t->timestamps();
            $t->unique(['deal_id', 'contact_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deal_contact');
        Schema::dropIfExists('deals');
    }
};