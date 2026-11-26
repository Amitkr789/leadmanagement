<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $t) {
            $t->id();
            $t->string('ticket_no', 20)->nullable()->unique();      // TKT-00012 (filled right after insert)
            $t->string('subject');
            $t->text('description')->nullable();
            $t->string('status', 20)->default('open')->index();     // open | in_progress | waiting | resolved | closed
            $t->string('priority', 10)->default('medium')->index(); // low | medium | high | urgent
            $t->string('category', 40)->default('General');
            $t->string('channel', 20)->nullable();                   // phone | whatsapp | email | walk_in | web
            $t->timestamp('due_at')->nullable()->index();            // SLA – when the first response / fix is due
            $t->timestamp('first_response_at')->nullable();
            $t->timestamp('resolved_at')->nullable();
            $t->timestamp('closed_at')->nullable();

            // Associations. A ticket created from a deal also stores that deal's contact + company, so
            // "all tickets of this contact / company" works no matter where the ticket was raised from.
            $t->foreignId('deal_id')->nullable()->constrained('deals')->nullOnDelete();
            $t->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $t->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $t->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();

            $t->foreignId('assigned_to')->nullable()->constrained('admins')->nullOnDelete();
            $t->foreignId('created_by')->nullable()->constrained('admins')->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('support_ticket_messages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ticket_id')->constrained('support_tickets')->cascadeOnDelete();
            $t->string('type', 10);                                  // reply (to customer) | note (internal) | system
            $t->text('body');
            $t->foreignId('created_by')->nullable()->constrained('admins')->nullOnDelete();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_ticket_messages');
        Schema::dropIfExists('support_tickets');
    }
};