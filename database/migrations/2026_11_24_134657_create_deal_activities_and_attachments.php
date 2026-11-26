<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Timeline: notes / calls / emails / meetings / tasks logged by people + "system" rows written automatically
        // (stage changes, field edits, files, associations).
        Schema::create('deal_activities', function (Blueprint $t) {
            $t->id();
            $t->foreignId('deal_id')->constrained()->cascadeOnDelete();
            $t->string('type', 20)->index();           // note | call | email | meeting | task | system
            $t->text('body');
            $t->json('meta')->nullable();               // email subject, call outcome, system "kind"
            $t->timestamp('activity_at')->nullable()->index();   // when it happened, or when it is due / scheduled
            $t->timestamp('completed_at')->nullable();           // tasks + meetings
            $t->foreignId('created_by')->nullable()->constrained('admins')->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('deal_attachments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('deal_id')->constrained()->cascadeOnDelete();
            $t->string('file_name');
            $t->string('file_path');
            $t->string('mime_type')->nullable();
            $t->unsignedBigInteger('file_size')->default(0);
            $t->foreignId('uploaded_by')->nullable()->constrained('admins')->nullOnDelete();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deal_attachments');
        Schema::dropIfExists('deal_activities');
    }
};