<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * UPDATED after seeing the real KbArticle model: `keywords` and
 * `search_tags` (the renamed `tags` column) already exist on kb_articles,
 * and a proper `tags()` belongsToMany-to-KbTag relation already exists
 * too. Adding a fresh `tags` JSON column here — as an earlier draft of
 * this migration did — would have collided with that relation's naming
 * and duplicated `search_tags`. So this migration no longer touches
 * kb_articles at all.
 *
 * The only column this still adds is chat_conversations.context_memory,
 * for Phase/Problem #7 (conversation memory). We deliberately do NOT
 * reuse the existing `state` / `state_data` columns for this: those are
 * already used by the scripted slot-filling flow (asking_product ->
 * asking_battery_type) and get overwritten by every setState()/clear()
 * call. Reusing them for long-term memory (focus product, last media)
 * would silently wipe that memory every time the scripted flow advances
 * or resets. A separate column keeps both concerns independent and
 * keeps setState()/clear() behavior 100% unchanged.
 *
 * IMPORTANT: if `chat_conversations` already has an equivalent JSON
 * column under a different name, skip this migration and point
 * ConversationService's `memory()` / `mergeMemory()` at that column
 * instead of `context_memory`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_conversations', function (Blueprint $table) {
            if (! Schema::hasColumn('chat_conversations', 'context_memory')) {
                $table->json('context_memory')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('chat_conversations', function (Blueprint $table) {
            if (Schema::hasColumn('chat_conversations', 'context_memory')) {
                $table->dropColumn('context_memory');
            }
        });
    }
};
