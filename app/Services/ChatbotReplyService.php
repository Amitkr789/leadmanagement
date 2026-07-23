<?php

namespace App\Services;

use App\Events\ConversationListUpdated;
use App\Events\NewChatMessage;
use App\Models\Admin;
use App\Models\ChatbotSetting;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Product;
use App\Notifications\ChatConversationNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Throwable;

/**
 * Same public surface as before — handleInboundMessage(ChatConversation,
 * ChatMessage): void — and the same constructor dependencies (OpenAiService,
 * KbEmbeddingService, WhatsAppService, ConversationService, IntentService).
 * No new service classes were introduced; everything below either lives in
 * this file or in the two services that already existed.
 *
 * WHAT CHANGED AND WHY (maps to the numbered problems in the brief):
 *
 *  #1/#2/#8  Context sent to GPT is now the single dominant article
 *            whenever one clearly outranks the rest, and GPT no longer
 *            reports "sufficient_context" at all — that decision is
 *            made from the hybrid score alone (CONFIDENCE_HIGH / _MEDIUM
 *            thresholds below), so it can never contradict itself again.
 *  #3        A cheap keyword check for "resend" intents ("phir se bhejo",
 *            "send again", "ek baar phir") answers straight from
 *            conversation memory — zero OpenAI calls.
 *  #4        finalizeReply() now attaches every media item on the used
 *            article(s), not just ones tagged "catalog".
 *  #5/#6     Delegated to the upgraded KbEmbeddingService (hybrid score +
 *            typo normalization + focus boost) — this file just passes
 *            the conversation's focus product into topMatches().
 *  #7        Reads/writes conversation memory via the new
 *            ConversationService methods (focus product, last article,
 *            last media, pending clarification).
 *  #9        Only the winning article(s) are sent as context (previously
 *            up to 5, concatenated), and the history window is trimmed.
 */
class ChatbotReplyService
{
    private const FALLBACK = [
        'en' => 'Our executive will contact you.',
        'hi' => 'हमारा प्रतिनिधि आपसे संपर्क करेगा।',
        'bn' => 'আমাদের প্রতিনিধি আপনার সাথে যোগাযোগ করবেন।',
    ];

    private const RESEND_KEYWORDS = [
        'phir se bhejo', 'ek baar phir', 'dubara bhejo', 'phir bhejo',
        'send again', 'resend', 'once more', 'again bhejo',
    ];

    private const HISTORY_WINDOW = 8; // trimmed from 12 to cut tokens
    private const RETRIEVAL_TOP_K = 5;
    private const MIN_RELEVANCE_SCORE = 0.45;

    // Score gap above which the #2-ranked article is considered
    // irrelevant noise and dropped from context entirely (fix for #1).
    private const DOMINANCE_GAP = 0.12;

    // Backend-owned confidence tiers (replaces GPT's own confidence/
    // sufficient_context — fix for #2).
    private const CONFIDENCE_HIGH = 0.80;
    private const CONFIDENCE_MEDIUM = 0.55;

    public function __construct(
        private OpenAiService $ai,
        private KbEmbeddingService $kb,
        private WhatsAppService $whatsapp,
        private ConversationService $conversation,
        private IntentService $intent,
    ) {
    }

    public function handleInboundMessage(ChatConversation $conversation, ChatMessage $inbound): void
    {
        $settings = ChatbotSetting::current();

        if (! $settings->ai_globally_enabled || ! $conversation->ai_active) {
            event(new ConversationListUpdated($conversation->fresh()));
            return;
        }

        $rawBody = (string) ($inbound->body ?? '');

        try {
            // Fast path: "send the catalog again" — answer straight from
            // memory, no OpenAI call at all.
            if ($this->looksLikeResendRequest($rawBody) && $this->handleResendFromMemory($conversation)) {
                return;
            }

            // Customer replying to a clarification question we asked last turn.
            if ($pending = $this->conversation->pendingClarification($conversation)) {
                $this->handleClarificationReply($conversation, $inbound, $pending);
                return;
            }

            // Legacy scripted slot-filling flow, unchanged behavior.
            $state = $this->conversation->state($conversation);

            if ($state && $this->handleConversationState($conversation, $inbound, $state)) {
                return;
            }

            // Canned ChatIntent keyword replies (greeting, human, etc.).
            if ($this->handleCannedIntent($conversation, $rawBody)) {
                return;
            }

            $this->handleRagPipeline($conversation, $inbound);
        } catch (Throwable $e) {
            Log::error('Chatbot pipeline crashed — escalating as a safety net', [
                'conversation_id' => $conversation->id,
                'error' => $e->getMessage(),
            ]);

            $language = $conversation->language ?? 'en';
            $replyText = $settings->{"fallback_message_{$language}"} ?: self::FALLBACK[$language] ?? self::FALLBACK['en'];
            $this->finalizeReply($conversation, $replyText, $language, 0, [], escalate: true);
        }
    }

    /* ==================================================================
       #3 — Resend from memory (no GPT call)
       ================================================================== */

    private function looksLikeResendRequest(string $body): bool
    {
        $lower = mb_strtolower($body);

        foreach (self::RESEND_KEYWORDS as $phrase) {
            if (str_contains($lower, $phrase)) {
                return true;
            }
        }

        return false;
    }

    private function handleResendFromMemory(ChatConversation $conversation): bool
    {
        $descriptors = $this->conversation->lastMedia($conversation);

        if (empty($descriptors)) {
            return false; // nothing remembered — fall through to normal RAG pipeline
        }

        // Descriptors were stored as plain arrays (see finalizeReply()) —
        // cast back to objects so sendMediaItems() can use the same
        // ->file_path / ->mime_type / ->file_name access it uses for
        // real Eloquent media models.
        $media = collect($descriptors)->map(fn (array $d) => (object) $d);

        $this->sendMediaItems($conversation, $media);

        $language = $conversation->language ?? 'en';
        $confirmations = [
            'en' => 'Sure, here it is again.',
            'hi' => 'Zaroor, yeh phir se bhej diya.',
            'bn' => 'নিশ্চয়ই, আবার পাঠালাম।',
        ];

        $this->finalizeReply(
            $conversation,
            $confirmations[$language] ?? $confirmations['en'],
            $language,
            100,
            [],
            escalate: false
        );

        return true;
    }

    /* ==================================================================
       Clarification (multi-product ambiguity) resolution
       ================================================================== */

    private function handleClarificationReply(ChatConversation $conversation, ChatMessage $inbound, array $pending): void
    {
        $language = $conversation->language ?? 'en';
        $candidateIds = $pending['candidate_product_ids'] ?? [];
        $resolved = $this->resolveProductSelection((string) $inbound->body, $candidateIds);

        if (! $resolved) {
            $question = $this->buildClarificationQuestion($candidateIds, $language);
            $this->finalizeReply($conversation, $question, $language, 0, [], escalate: false);
            return;
        }

        $this->conversation->clearPendingClarification($conversation);
        $this->conversation->rememberFocusProduct($conversation, $resolved->id);

        $originalQuestion = $pending['question'] ?? $resolved->name;
        $scopedQuery = trim($resolved->name . ' ' . $originalQuestion);

        $this->handleRagPipeline($conversation, $inbound, $scopedQuery);
    }

    private function resolveProductSelection(string $reply, array $candidateIds): ?Product
    {
        $products = Product::query()->whereIn('id', $candidateIds)->get(['id', 'name'])->values();
        $lower = mb_strtolower(trim($reply));

        if (preg_match('/(\d+)/', $lower, $m)) {
            $index = ((int) $m[1]) - 1;
            if ($products->has($index)) {
                return $products->get($index);
            }
        }

        $replyTokens = collect(preg_split('/\s+/u', $lower))->filter();

        $best = $products
            ->map(function (Product $p) use ($replyTokens) {
                $nameTokens = collect(preg_split('/\s+/u', mb_strtolower($p->name)))->filter();
                return ['product' => $p, 'overlap' => $nameTokens->intersect($replyTokens)->count()];
            })
            ->sortByDesc('overlap')
            ->first();

        return ($best && $best['overlap'] > 0) ? $best['product'] : null;
    }

    private function buildClarificationQuestion(array $candidateIds, string $language): string
    {
        $products = Product::query()->whereIn('id', $candidateIds)->get(['id', 'name']);
        $lines = $products->values()->map(fn (Product $p, int $i) => ($i + 1) . ') ' . $p->name)->implode("\n");

        $prompts = [
            'en' => "We have a few matching options — which one did you mean?\n{$lines}",
            'hi' => "Hamare paas kuch matching options hain — aapko kaunsa chahiye?\n{$lines}",
            'bn' => "আমাদের কাছে বেশ কয়েকটি অপশন আছে — আপনি কোনটি বলছেন?\n{$lines}",
        ];

        return $prompts[$language] ?? $prompts['en'];
    }

    /* ==================================================================
       Legacy scripted slot-filling flow (unchanged from the original file)
       ================================================================== */

    private function handleConversationState(ChatConversation $conversation, ChatMessage $inbound, string $state): bool
    {
        $msg = mb_strtolower((string) $inbound->body);

        switch ($state) {
            case 'asking_product':
                if (str_contains($msg, 'battery')) {
                    $this->conversation->setState($conversation, 'asking_battery_type');
                    $this->finalizeReply($conversation, 'Lithium Battery chahiye ya Lead Acid Battery?', 'en', 100, [], false);
                    return true;
                }
                if (str_contains($msg, 'scooter')) {
                    $this->conversation->clear($conversation);
                    $this->finalizeReply($conversation, 'Hamare paas multiple Electric Scooters available hain. Aapka budget kitna hai?', 'en', 100, [], false);
                    return true;
                }
                break;

            case 'asking_battery_type':
                if (str_contains($msg, 'lithium')) {
                    $this->conversation->clear($conversation);
                    $this->finalizeReply($conversation, 'Great 😊 48V, 60V aur 72V Lithium Batteries available hain. Aapko kis vehicle ke liye chahiye?', 'en', 100, [], false);
                    return true;
                }
                break;
        }

        return false;
    }

    private function handleCannedIntent(ChatConversation $conversation, string $rawBody): bool
    {
        $intent = $this->intent->detect($rawBody);

        if (! $intent) {
            return false;
        }

        $intentRow = \App\Models\ChatIntent::where('intent', $intent)->first();

        if (! $intentRow || empty($intentRow->responses)) {
            return false;
        }

        $reply = $intentRow->responses[array_rand($intentRow->responses)];

        if ($intent === 'greeting') {
            $this->conversation->setState($conversation, 'asking_product');
        }

        $this->finalizeReply($conversation, $reply, 'en', 100, [], false);

        return true;
    }

    /* ==================================================================
       Core RAG pipeline
       ================================================================== */

    private function handleRagPipeline(ChatConversation $conversation, ChatMessage $inbound, ?string $overrideQuery = null): void
    {
        $settings = ChatbotSetting::current();
        $language = $conversation->language ?? 'en';

        $searchQuery = $overrideQuery ?? (string) $inbound->body;
        $focusProductId = $this->conversation->focusProductId($conversation);

        // Follow-up expansion: "price?" -> use remembered focus product
        // as extra context for retrieval.
        if (! $overrideQuery && $focusProductId && $this->conversation->looksLikeFollowUp($searchQuery)) {
            $product = Product::find($focusProductId);
            if ($product) {
                $searchQuery = trim($product->name . ' ' . $searchQuery);
            }
        }

        $matches = $this->kb->topMatches($searchQuery, self::RETRIEVAL_TOP_K, self::MIN_RELEVANCE_SCORE, $focusProductId);

        Log::info('KB HYBRID MATCH', [
            'question' => $inbound->body,
            'search_query' => $searchQuery,
            'titles' => $matches->pluck('article.title')->toArray(),
            'scores' => $matches->pluck('score')->toArray(),
        ]);

        if ($matches->isEmpty()) {
            $replyText = $settings->{"fallback_message_{$language}"} ?: self::FALLBACK[$language] ?? self::FALLBACK['en'];
            $this->finalizeReply($conversation, $replyText, $language, 0, [], escalate: true);
            return;
        }

        // #1 fix — multiple DIFFERENT products matched a generic query
        // ("price" with 5 products) -> ask instead of letting GPT guess.
        $distinctProductIds = $matches->pluck('article.product_id')->filter()->unique()->values();

        if ($distinctProductIds->count() > 1) {
            $this->conversation->setPendingClarification($conversation, (string) $inbound->body, $distinctProductIds->all());
            $question = $this->buildClarificationQuestion($distinctProductIds->all(), $language);
            $this->finalizeReply($conversation, $question, $language, 0, [], escalate: false);
            return;
        }

        // #1/#9 fix — send only the dominant article's context. A
        // second article is included ONLY if its score is close enough
        // to genuinely be relevant too (same product, so no wrong-KB risk).
        $top = $matches->first();
        $contextArticles = $matches->filter(fn ($m) => ($top['score'] - $m['score']) <= self::DOMINANCE_GAP);

        $topScore = (float) $top['score'];
        $usedArticleIds = $contextArticles->pluck('article.id')->values()->all();

        // #2 fix — LOW tier never calls GPT at all.
        if ($topScore < self::CONFIDENCE_MEDIUM) {
            $replyText = $settings->{"fallback_message_{$language}"} ?: self::FALLBACK[$language] ?? self::FALLBACK['en'];
            $this->finalizeReply($conversation, $replyText, $language, $this->toPercent($topScore), $usedArticleIds, escalate: true);
            return;
        }

        $isHighConfidence = $topScore > self::CONFIDENCE_HIGH;
        $context = $contextArticles->map(fn ($m) => $m['article']->toAiContext())->implode("\n\n---\n\n");

        $messages = array_merge(
            [['role' => 'system', 'content' => $this->buildSystemPrompt($settings, $context, $isHighConfidence, $language)]],
            $this->recentHistory($conversation)
        );

        try {
            $result = $this->ai->chatJson($messages);
        } catch (Throwable $e) {
            Log::error('Chatbot AI request failed', ['conversation_id' => $conversation->id, 'error' => $e->getMessage()]);
            $result = [];
        }

        $modelAnswer = trim((string) ($result['answer'] ?? ''));
        $resultLanguage = in_array($result['language'] ?? null, ['en', 'hi', 'bn'], true) ? $result['language'] : $language;

        // #2 fix — in MEDIUM tier only, GPT may flag "this context doesn't
        // actually answer it" — never a self-reported confidence number.
        $answerable = $isHighConfidence ? true : (bool) ($result['answerable'] ?? false);
        $shouldEscalate = $modelAnswer === '' || ! $answerable;

        $replyText = $shouldEscalate
            ? ($settings->{"fallback_message_{$resultLanguage}"} ?: self::FALLBACK[$resultLanguage] ?? self::FALLBACK['en'])
            : $modelAnswer;

        if (! $shouldEscalate) {
            $this->conversation->rememberLastArticle($conversation, $top['article']->id);

            if ($top['article']->product_id) {
                $this->conversation->rememberFocusProduct($conversation, $top['article']->product_id);
            }
        }

        $this->finalizeReply($conversation, $replyText, $resultLanguage, $this->toPercent($topScore), $usedArticleIds, $shouldEscalate, $shouldEscalate ? collect() : $contextArticles);
    }

    private function toPercent(float $score): int
    {
        return (int) round(min(1.0, max(0.0, $score)) * 100);
    }

    /* ==================================================================
       Simplified prompt — GPT rewrites/translates only, never decides
       retrieval quality (fix for #8). Only the winning article(s) are
       included (fix for #9 — token minimization).
       ================================================================== */

    private function buildSystemPrompt(ChatbotSetting $settings, string $context, bool $isHighConfidence, string $language): string
    {
        $brandInstructions = trim((string) $settings->system_prompt);
        $languageNames = ['en' => 'English', 'hi' => 'Hindi', 'bn' => 'Bengali'];
        $languageName = $languageNames[$language] ?? 'English';

        $schema = $isHighConfidence
            ? '{"language":"en|hi|bn","answer":"..."}'
            : '{"language":"en|hi|bn","answer":"...","answerable":true|false}';

        $answerableRule = $isHighConfidence
            ? ''
            : "\nIf CONTEXT does not actually answer the latest question, set \"answerable\":false and leave \"answer\" empty.\n";

        return <<<PROMPT
WhatsApp support assistant. Reply in {$languageName}.
{$brandInstructions}

Use ONLY CONTEXT below as facts. Never invent numbers/specs/policies. History is only for follow-up context, not facts. Keep it short and natural — do not mention "context" or that you're an AI.
{$answerableRule}
Return ONLY JSON: {$schema}

CONTEXT
{$context}
PROMPT;
    }

    private function recentHistory(ChatConversation $conversation): array
    {
        return $conversation->messages()
            ->where(function ($q) {
                $q->where('sender_type', 'customer')
                    ->orWhere(function ($q2) {
                        $q2->where('sender_type', 'ai')->where('ai_confidence', '>=', 60);
                    });
            })
            ->orderByDesc('id')
            ->limit(self::HISTORY_WINDOW)
            ->get()
            ->reverse()
            ->map(fn (ChatMessage $m) => [
                'role' => $m->sender_type === 'customer' ? 'user' : 'assistant',
                'content' => (string) $m->body,
            ])
            ->values()
            ->all();
    }

    /* ==================================================================
       #4 fix — auto-send ALL media types attached to the used article(s),
       and remember what was sent for the #3 resend fast-path.
       ================================================================== */

    private function sendMediaItems(ChatConversation $conversation, Collection $media): void
    {
        foreach ($media as $item) {
            try {
                $this->whatsapp->sendMedia(
                    $conversation->wa_phone_number,
                    $this->waMediaTypeFor($item->mime_type ?? 'application/pdf'),
                    storage_path('app/public/' . $item->file_path),
                    $item->mime_type ?? 'application/pdf',
                    $item->file_name ?? null
                );
            } catch (Throwable $e) {
                Log::error('Media send failed', ['error' => $e->getMessage()]);
            }
        }
    }

    private function waMediaTypeFor(string $mime): string
    {
        if (str_starts_with($mime, 'image/')) return 'image';
        if (str_starts_with($mime, 'video/')) return 'video';
        if (str_starts_with($mime, 'audio/')) return 'audio';
        return 'document';
    }

    /* ==================================================================
       Shared reply plumbing — same behavior as the existing
       finalizeReply(), extended to send every media item on the used
       article(s) instead of only ones tagged "catalog".
       ================================================================== */

    private function finalizeReply(
        ChatConversation $conversation,
        string $replyText,
        string $language,
        int $confidence,
        array $kbArticleIds,
        bool $escalate,
        ?Collection $contextArticles = null,
    ): void {
        $waMessageId = null;

        try {
            $waMessageId = $this->whatsapp->sendText($conversation->wa_phone_number, $replyText);
        } catch (Throwable $e) {
            Log::error('WhatsApp send failed during AI reply', ['error' => $e->getMessage()]);
        }

        if ($contextArticles && $contextArticles->isNotEmpty() && ! $escalate) {
            $media = $contextArticles->flatMap(fn ($m) => $m['article']->media ?? collect())->unique('id');

            if ($media->isNotEmpty()) {
                $this->sendMediaItems($conversation, $media);

                $descriptors = $media->map(fn ($item) => [
                    'id' => $item->id,
                    'file_path' => $item->file_path,
                    'mime_type' => $item->mime_type ?? null,
                    'file_name' => $item->file_name ?? null,
                ])->all();

                $this->conversation->rememberLastMedia($conversation, $descriptors);
            }
        }

        $outbound = $conversation->messages()->create([
            'direction' => 'outbound',
            'sender_type' => 'ai',
            'type' => 'text',
            'body' => $replyText,
            'wa_message_id' => $waMessageId,
            'wa_status' => $waMessageId ? 'sent' : 'failed',
            'ai_confidence' => $confidence,
            'kb_article_ids' => $kbArticleIds,
            'language' => $language,
        ]);

        $conversation->forceFill([
            'language' => $language,
            'last_message_at' => now(),
            'last_message_preview' => Str::limit($replyText, 80),
        ]);

        if ($escalate) {
            $conversation->forceFill(['ai_active' => false, 'escalated_at' => now()]);
        }

        $conversation->save();

        broadcast(new NewChatMessage($outbound))->toOthers();
        event(new ConversationListUpdated($conversation->fresh()));

        if ($escalate) {
            $this->notifyEscalation($conversation);
        }
    }

    private function notifyEscalation(ChatConversation $conversation): void
    {
        try {
            $recipients = $conversation->assigned_admin_id
                ? Admin::query()->where('id', $conversation->assigned_admin_id)->get()
                : Admin::query()->where('role', 'super_admin')->get();

            if ($recipients->isNotEmpty()) {
                Notification::send($recipients, new ChatConversationNotification($conversation, 'escalated'));
            }
        } catch (Throwable $e) {
            Log::error('Escalation notification failed', [
                'conversation_id' => $conversation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
