<?php

namespace App\Services;

use App\Models\KbArticle;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
/**
 * Retrieval half of "never hallucinate": the chatbot only ever sees the
 * handful of KB articles that are actually relevant to the customer's
 * question, never the whole KB.
 *
 * CHANGES IN THIS REVISION (kept inside this same class — no new service
 * added, per "don't invent unnecessary services"):
 *
 *  1. Typo normalization — a small static map fixes the common misspellings
 *     ("catelog", "battry", "waranty", ...) before anything else runs.
 *     O(1) per token, no DB/fuzzy lookup, so retrieval speed is unaffected.
 *
 *  2. Hybrid score — final_score is now a weighted blend instead of pure
 *     embedding similarity:
 *
 *       final = 0.55 * embedding
 *             + 0.20 * keyword match   (kb_articles.keywords — already
 *                                        exists on your model, cast to array)
 *             + 0.10 * tag match       (kb_articles.search_tags JSON, merged
 *                                        with the structured tags() relation
 *                                        to KbTag — your real schema has both)
 *             + 0.15 * product-name match
 *
 *  3. Conversation focus boost — an optional 4th parameter lets the caller
 *     pass in the product the conversation is currently focused on
 *     (from ConversationService). Articles belonging to that product get
 *     a small score bump, so a short follow-up like "warranty?" is more
 *     likely to resolve to the product already being discussed.
 *
 * SCHEMA NOTE: no migration is needed for this file at all — `keywords`
 * and `search_tags` already exist on kb_articles (per your real KbArticle
 * model), and `tags()` is already a proper belongsToMany to KbTag. The
 * tags relation is eager-loaded up front (`with('tags:id,name')`) so
 * scoring every article doesn't trigger an N+1 query.
 */
class KbEmbeddingService
{
    private const WEIGHT_EMBEDDING = 0.55;
    private const WEIGHT_KEYWORD = 0.20;
    private const WEIGHT_TAG = 0.10;
    private const WEIGHT_PRODUCT_NAME = 0.15;

    private const FOCUS_BOOST = 0.10;

    /** Grows over time from analytics / support feedback — same idea as
     *  before, just no longer a separate service class. */
    private const TYPO_MAP = [
        'catelog' => 'catalog', 'catalogue' => 'catalog', 'catelogue' => 'catalog',
        'catelouge' => 'catalog', 'katalog' => 'catalog',
        'brocher' => 'brochure', 'brochur' => 'brochure', 'broucher' => 'brochure',
        'battry' => 'battery', 'baterry' => 'battery', 'bettery' => 'battery', 'batery' => 'battery',
        'lithum' => 'lithium', 'lithiam' => 'lithium', 'lithiyam' => 'lithium',
        'waranty' => 'warranty', 'warrenty' => 'warranty', 'warrnty' => 'warranty','warantty'=>'Warranty',
        'gaurantee' => 'guarantee', 'garanty' => 'guarantee',
        'delivary' => 'delivery', 'deliverry' => 'delivery',
        'stok' => 'stock', 'stcok' => 'stock',
        'chargr' => 'charger', 'chrger' => 'charger',

    ];

    public function __construct(private OpenAiService $ai,
     private QueryNormalizerService $normalizer)
    {
    }

    /**
     * Recomputes and caches the embedding for a KB article if, and only
     * if, its AI-facing content actually changed since last time.
     * (Unchanged from the existing implementation.)
     */
    public function syncArticle(KbArticle $article): void
    {
        $eligible = $article->status === 'published' && $article->ai_ready;

        if (! $eligible) {
            if ($article->embedding) {
                $article->forceFill([
                    'embedding' => null,
                    'embedding_hash' => null,
                    'embedding_updated_at' => null,
                ])->saveQuietly();
            }

            return;
        }

        $text = $article->toAiContext();
        $hash = md5($text);

        if ($article->embedding_hash === $hash) {
            return;
        }

        $vector = $this->ai->embed($text);

        $article->forceFill([
            'embedding' => json_encode($vector),
            'embedding_hash' => $hash,
            'embedding_updated_at' => now(),
        ])->saveQuietly();
    }

    /**
     * Returns the top-K KB articles most relevant to $query, each paired
     * with its hybrid score (0..1).
     *
     * Signature is backward compatible: existing callers that only pass
     * 3 args behave identically to before (focus boost simply doesn't
     * apply). $focusProductId is new and optional.
     *
     * @return Collection<int, array{article: KbArticle, score: float}>
     */
    public function topMatches(string $query, int $topK = 5, float $minScore = 0.45, ?int $focusProductId = null): Collection
    {

        // $normalizedQuery = $this->normalizeTypos($query);
        // $queryTokens = $this->tokenize($normalizedQuery);
    $normalized = $this->normalizer->normalize($query);

$normalizedQuery = $normalized['normalized'];
$queryTokens = collect($normalized['tokens']);
        $queryVector = $this->ai->embed($normalizedQuery);
Log::info('SEARCH START',[
    'query'=>$query,
    'normalized'=>$normalizedQuery,
    'tokens'=>$queryTokens->values()->all(),
]);
        // Precompute which articles belong to a product whose name the
        // query mentions — used both as a scoring signal and as a
        // guaranteed-include safety net (see below), same guarantee the
        // original matchByProductName() gave.
        $productNameArticleIds = $this->matchByProductName($normalizedQuery)->pluck('id')->flip();
    $articles = KbArticle::query()
    ->aiReady()
    ->whereNotNull('embedding')
    ->with('tags:id,name')
    ->get();

Log::info('TOTAL ARTICLES',[
    'count'  => $articles->count(),
    'titles' => $articles->pluck('title')->all(),
]);
if ($articles->count() === 1) {
    return collect([
        [
            'article' => $articles->first(),
            'score' => 1.0
        ]
    ]);
}
        $semantic = KbArticle::query()
            ->aiReady()
            ->whereNotNull('embedding')
            // Eager-load the tags relation up front — without this, calling
            // $article->tags inside the map() below would fire one query
            // PER article (N+1), which is exactly the "don't make retrieval
            // slow" trap for a 10k-article KB.
            ->with(['tags:id,name'])
            ->get()
            ->map(function (KbArticle $article) use ($queryVector, $queryTokens, $productNameArticleIds, $focusProductId) {
                $embeddingScore = $this->cosineSimilarity($queryVector, json_decode($article->embedding, true) ?: []);

                $keywordScore = $this->tokenOverlapRatio($queryTokens, $this->arrayTokens($article->keywords));
                $tagScore = $this->tokenOverlapRatio($queryTokens, $this->articleTagTokens($article));
                $productScore = $productNameArticleIds->has($article->id) ? 1.0 : 0.0;

                $score = (self::WEIGHT_EMBEDDING * $embeddingScore)
                    + (self::WEIGHT_KEYWORD * $keywordScore)
                    + (self::WEIGHT_TAG * $tagScore)
                    + (self::WEIGHT_PRODUCT_NAME * $productScore);

                if ($focusProductId && $article->product_id === $focusProductId) {
                    $score = min(1.0, $score + self::FOCUS_BOOST);
                }
                 Log::info('ARTICLE SCORE',[
        'title'=>$article->title,
        'embedding'=>$embeddingScore,
        'keyword'=>$keywordScore,
        'tag'=>$tagScore,
        'product'=>$productScore,
        'final'=>$score,
    ]);

                return ['article' => $article, 'score' => round($score, 4)];
            })
            // ->filter(fn ($r) => $r['score'] >= $minScore)
            ->sortByDesc('score')
            ->values();
            $bestScore = $semantic->max('score');

$threshold = max(
    min($minScore, $bestScore - 0.08),
    0.25
);
$semantic = $semantic
    ->filter(fn($r) => $r['score'] >= $threshold)
    ->values();

        if ($semantic->isNotEmpty()) {
            $topScore = $semantic->first()['score'];

            // Only keep articles close to the best match, so a strong hit
            // doesn't get diluted by weak long-tail matches.
            $semantic = $semantic->filter(fn ($row) => $row['score'] >= ($topScore - 0.08))->values();
        }

        // Guaranteed-include safety net: an article whose product was
        // explicitly named should never be dropped by an unlucky
        // embedding score, even if it fell under $minScore above.
        $forced = KbArticle::query()
            ->whereIn('id', $productNameArticleIds->keys())
            ->aiReady()
            ->get()
            ->map(fn (KbArticle $article) => ['article' => $article, 'score' => 0.95]);

        return $semantic
            ->merge($forced)
            ->unique(fn ($r) => $r['article']->id)
            ->sortByDesc('score')
            ->take($topK)
            ->values();
    }

    private function matchByProductName(string $query): Collection
    {
        $queryTokens = $this->tokenize($query);

        return Product::all(['id', 'name'])
            ->filter(function ($p) use ($queryTokens) {
                $nameTokens = $this->tokenize((string) $p->name)
                    ->reject(fn ($t) => in_array($t, ['ion', 'the', 'of', 'a']));
                $overlap = $nameTokens->intersect($queryTokens)->count();

                return $nameTokens->count() > 0
                    && $overlap >= max(1, (int) ceil($nameTokens->count() / 2));
            })
            ->flatMap(fn ($p) => KbArticle::aiReady()->where('product_id', $p->id)->get(['id']))
            ->values();
    }

    /**
     * Small static-dictionary typo fix. Deliberately simple (no fuzzy
     * distance search against the DB) so it stays fast at any KB size —
     * add new entries to TYPO_MAP as analytics/support surface them.
     */
    private function normalizeTypos(string $query): string
    {
        $words = preg_split('/(\s+)/u', $query, -1, PREG_SPLIT_DELIM_CAPTURE);

        foreach ($words as &$word) {
            $lower = mb_strtolower($word);
            if (isset(self::TYPO_MAP[$lower])) {
                $word = self::TYPO_MAP[$lower];
            }
        }

        return implode('', $words);
    }

    private function tokenize(string $text): Collection
    {
        $clean = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', mb_strtolower($text));

        return collect(preg_split('/\s+/u', trim((string) $clean)))->filter()->values();
    }

    /**
     * `keywords` is already cast to `array` on the KbArticle model, so this
     * just needs a defensive fallback in case it's ever null/raw.
     */
    private function arrayTokens($value): Collection
    {
        $values = is_array($value) ? $value : (json_decode((string) $value, true) ?: []);

        return collect($values)->map(fn ($v) => mb_strtolower(trim((string) $v)))->filter();
    }

    /**
     * Your real schema has TWO tag sources: the free-text `search_tags`
     * JSON column, and the structured `tags()` belongsToMany to KbTag.
     * We combine both so an admin can tag an article either way and it
     * still improves retrieval. Relies on `tags` being eager-loaded by
     * the caller (topMatches()) to avoid N+1.
     */
    private function articleTagTokens(KbArticle $article): Collection
    {
        $structured = $article->relationLoaded('tags')
            ? $article->tags->pluck('name')
            : collect(); // defensive — should always be loaded via with('tags')

        return $structured
            ->merge($this->arrayTokens($article->search_tags))
            ->map(fn ($v) => mb_strtolower(trim((string) $v)))
            ->filter()
            ->unique()
            ->values();
    }

    private function tokenOverlapRatio(Collection $queryTokens, Collection $targetTokens): float
    {
        if ($queryTokens->isEmpty() || $targetTokens->isEmpty()) {
            return 0.0;
        }

        return min(1.0, $queryTokens->intersect($targetTokens)->count() / $queryTokens->count());
    }

    private function cosineSimilarity(array $a, array $b): float
    {
        if (empty($a) || empty($b) || count($a) !== count($b)) {
            return 0.0;
        }

        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        foreach ($a as $i => $val) {
            $dot += $val * $b[$i];
            $normA += $val * $val;
            $normB += $b[$i] * $b[$i];
        }

        if ($normA == 0.0 || $normB == 0.0) {
            return 0.0;
        }

        return $dot / (sqrt($normA) * sqrt($normB));
    }
}
