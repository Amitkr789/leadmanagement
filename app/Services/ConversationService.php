<?php

namespace App\Services;

use App\Models\ChatConversation;

/**
 * EXISTING behavior (same signatures, bug-fixed internals — see below):
 *   setState() / clear() / state()  -> scripted slot-filling flow control.
 *
 * NEW in this revision — conversation memory, stored in a separate
 * `context_memory` JSON column so it never gets wiped by setState()/
 * clear() calls the scripted flow already makes. Four fields, on
 * purpose kept minimal (no "becoming complex", per the brief):
 *
 *   - focus_product_id     the product currently being discussed
 *   - last_article_id      the KB article that answered the last question
 *   - last_media           media (ids) sent as part of the last answer
 *   - pending_clarification  a disambiguation question we're waiting on
 *
 * IMPORTANT FIX: your real ChatConversation model's $fillable does NOT
 * include `state`, `state_data`, or `context_memory` — so every call
 * this class previously made via `$conversation->update([...])` was
 * being silently dropped by Laravel's mass-assignment protection
 * (nothing errors, it just never persists). That means the pre-existing
 * scripted flow (asking_product -> asking_battery_type) likely never
 * actually worked in production either — this wasn't introduced by the
 * memory feature, it was already there.
 *
 * Fix: every write in this class now goes through `forceFill(...)->save()`
 * instead of `update(...)`, which bypasses mass-assignment guarding
 * entirely — the same pattern KbArticle/KbEmbeddingService already use
 * for their own system-managed fields (embedding, embedding_hash, ...).
 * These three fields are internal bookkeeping, never set directly from
 * a form/request, so forceFill() is the right tool here — no need to
 * widen $fillable (and no risk of accidentally exposing them to mass
 * assignment from user input elsewhere).
 *
 * SCHEMA NOTE: `context_memory` also needs a `'context_memory' => 'array'`
 * cast added to the model (same as the existing `'state_data' => 'array'`
 * cast) — without it, Eloquent can't serialize the PHP array to JSON on
 * save. That's the one small model change still required.
 */
class ConversationService
{
    public function setState(ChatConversation $conversation, string $state, array $data = []): void
    {
        $conversation->forceFill([
            'state' => $state,
            'state_data' => $data,
        ])->save();
    }

    public function clear(ChatConversation $conversation): void
    {
        $conversation->forceFill([
            'state' => null,
            'state_data' => [],
        ])->save();
    }

    public function state(ChatConversation $conversation): ?string
    {
        return $conversation->state;
    }

    /* ==================================================================
       NEW — conversation memory (context_memory column)
       ================================================================== */

    public function rememberFocusProduct(ChatConversation $conversation, int $productId): void
    {
        $this->mergeMemory($conversation, ['focus_product_id' => $productId]);
    }

    public function focusProductId(ChatConversation $conversation): ?int
    {
        return $this->memory($conversation)['focus_product_id'] ?? null;
    }

    public function rememberLastArticle(ChatConversation $conversation, int $articleId): void
    {
        $this->mergeMemory($conversation, ['last_article_id' => $articleId]);
    }

    public function lastArticleId(ChatConversation $conversation): ?int
    {
        return $this->memory($conversation)['last_article_id'] ?? null;
    }

    /**
     * Stores full media descriptors (not just IDs) — id/file_path/
     * mime_type/file_name — so a resend later doesn't depend on knowing
     * which Eloquent model your media table actually uses.
     *
     * @param array<int, array{id:int, file_path:string, mime_type:?string, file_name:?string}> $mediaDescriptors
     */
    public function rememberLastMedia(ChatConversation $conversation, array $mediaDescriptors): void
    {
        $this->mergeMemory($conversation, ['last_media' => array_values($mediaDescriptors)]);
    }

    /** @return array<int, array{id:int, file_path:string, mime_type:?string, file_name:?string}> */
    public function lastMedia(ChatConversation $conversation): array
    {
        return $this->memory($conversation)['last_media'] ?? [];
    }

    /** @param array<int,int> $candidateProductIds */
    public function setPendingClarification(ChatConversation $conversation, string $originalQuestion, array $candidateProductIds): void
    {
        $this->mergeMemory($conversation, [
            'pending_clarification' => [
                'question' => $originalQuestion,
                'candidate_product_ids' => $candidateProductIds,
            ],
        ]);
    }

    public function pendingClarification(ChatConversation $conversation): ?array
    {
        return $this->memory($conversation)['pending_clarification'] ?? null;
    }

    public function clearPendingClarification(ChatConversation $conversation): void
    {
        $this->mergeMemory($conversation, ['pending_clarification' => null]);
    }

    /**
     * A short customer message that, on its own, doesn't specify a
     * product — the kind of query where remembered focus should fill
     * the gap ("price?", "warranty kitni hai").
     */
    public function looksLikeFollowUp(string $query): bool
    {
        $wordCount = count(array_filter(explode(' ', trim($query))));

        return $wordCount > 0 && $wordCount <= 3;
    }

    private function memory(ChatConversation $conversation): array
    {
        $raw = $conversation->context_memory;

        return is_array($raw) ? $raw : (json_decode((string) $raw, true) ?: []);
    }

    private function mergeMemory(ChatConversation $conversation, array $patch): void
    {
        $conversation->forceFill([
            'context_memory' => array_merge($this->memory($conversation), $patch),
        ])->save();
    }
}
