<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Who did what to a company, and when. Removed together with the company.
        Schema::create('company_activities', function (Blueprint $t) {
            $t->id();
            $t->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $t->foreignId('super_admin_id')->nullable()->constrained('super_admins')->nullOnDelete();
            $t->string('type', 30);        // created, updated, upgraded, trial_extended, suspended, reactivated, payment
            $t->string('message', 255);
            $t->timestamp('created_at')->useCurrent();
            $t->index(['company_id', 'id']);
        });

        // Invoices survive company deletion (accounting), so details are snapshotted.
        Schema::create('invoices', function (Blueprint $t) {
            $t->id();
            $t->string('invoice_no')->unique();
            $t->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $t->string('company_name');
            $t->string('company_gst', 15)->nullable();
            $t->string('company_address', 400)->nullable();
            $t->string('plan_name', 60);
            $t->string('billing_cycle', 10);
            $t->string('description');
            $t->date('period_start');
            $t->date('period_end');
            $t->unsignedInteger('subtotal');
            $t->unsignedTinyInteger('gst_percent');
            $t->unsignedInteger('gst_amount');
            $t->unsignedInteger('total');
            $t->string('status', 10)->default('pending')->index(); // pending | paid
            $t->string('payment_mode', 20)->nullable();
            $t->timestamp('paid_at')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('super_admins')->nullOnDelete();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('invoices'); Schema::dropIfExists('company_activities'); }
};
