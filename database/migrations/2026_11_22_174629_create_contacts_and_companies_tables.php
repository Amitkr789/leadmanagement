<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->index();
            $table->string('website', 255)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('gst_number', 30)->nullable();
            $table->string('address', 500)->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->index();
            $table->string('email', 255)->nullable()->index();
            $table->string('phone', 30)->nullable();
            $table->string('phone_key', 20)->nullable()->index();   // last 10 digits — used to spot duplicates
            $table->string('whatsapp', 30)->nullable();
            $table->string('job_title', 120)->nullable();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
        });

        Schema::table('leads', function (Blueprint $table) {
            if (!Schema::hasColumn('leads', 'contact_id')) {
                $table->unsignedBigInteger('contact_id')->nullable()->index();
            }
            if (!Schema::hasColumn('leads', 'company_id')) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            }
            if (!Schema::hasColumn('leads', 'email')) {
                $table->string('email', 255)->nullable();
            }
            if (!Schema::hasColumn('leads', 'company_name')) {
                $table->string('company_name', 255)->nullable();
            }
            if (!Schema::hasColumn('leads', 'gst_number')) {
                $table->string('gst_number', 30)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            foreach (['contact_id', 'company_id'] as $col) {
                if (Schema::hasColumn('leads', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('companies');
    }
};