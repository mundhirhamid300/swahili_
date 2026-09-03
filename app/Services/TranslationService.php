<?php

/** Hii service ina mantiki ya biashara inayotumiwa na sehemu hii ya mfumo. */

namespace App\Services;

use App\Exceptions\AiServiceException;
use App\Models\Flashcard;
use App\Models\TranslationLog;
use App\Models\User;
use App\Services\Ai\OpenAiLanguageService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TranslationService
{
    public function __construct(
        private OpenAiLanguageService $languageService,
        private AiQuotaService $quota,
        private XpService $xpService
    ) {}

    public function translate(string $text, string $from, string $to, ?User $user = null): array
    {
        $from = strtolower($from);
        $to = strtolower($to);
        $normalized = mb_strtolower(trim($text));

        $result = $this->lookupDictionary($normalized, $from, $to)
            ?? $this->lookupFlashcards($normalized, $from, $to)
            ?? $this->lookupCachedTranslation($normalized, $from, $to)
            ?? $this->translateWithOpenAi($text, $from, $to, $user);

        $this->logTranslation($user, $text, $result, $from, $to);

        // Award XP only for paid OpenAI translations — avoids 2 DB writes on every dictionary hit
        if ($user && ($result['source'] ?? null) === 'openai') {
            $this->xpService->award($user, 'translate', 1, mb_substr($text, 0, 40));
        }

        return $result;
    }

    /**
     * @return array{content: string, tokens_used: int|null, mode: string}
     */
    public function analyze(string $mode, string $text, string $from = 'en', string $to = 'sw', ?User $user = null): array
    {
        if (! $this->languageService->isAvailable()) {
            throw new AiServiceException('AI language tools are unavailable. Configure OPENAI_API_KEY.', 'openai');
        }

        if ($user) {
            $this->quota->assertWithinQuota($user);
        }

        $result = $this->languageService->runMode($mode, $text, $from, $to);

        if ($user) {
            $this->quota->consume($user, 'analyze_'.$mode, $result['tokens_used']);
            $this->xpService->award($user, 'analyze', 2, $mode);
        }

        return [
            'content' => $result['content'],
            'tokens_used' => $result['tokens_used'],
            'mode' => $mode,
        ];
    }

    public function getQuickPhrases(): array
    {
        return config('swahili.quick_phrases', []);
    }

    public function getDictionary(): array
    {
        return config('swahili.dictionary', []);
    }

    public function toggleFavorite(User $user, TranslationLog $log): TranslationLog
    {
        if ($log->user_id !== $user->id) {
            abort(403);
        }

        $log->update(['is_favorite' => ! $log->is_favorite]);

        return $log->fresh();
    }

    private function lookupDictionary(string $text, string $from, string $to): ?array
    {
        $dictionary = config('swahili.dictionary', []);

        if (! isset($dictionary[$text])) {
            return null;
        }

        $entry = $dictionary[$text];

        if ($from === 'en' && $to === 'sw') {
            return [
                'translation' => $entry['sw'],
                'pronunciation' => $entry['pronunciation'] ?? null,
                'source' => 'dictionary',
                'tokens_used' => null,
            ];
        }

        if ($from === 'sw' && $to === 'en') {
            return [
                'translation' => $entry['en'],
                'pronunciation' => $entry['pronunciation'] ?? null,
                'source' => 'dictionary',
                'tokens_used' => null,
            ];
        }

        return null;
    }

    private function lookupFlashcards(string $text, string $from, string $to): ?array
    {
        $query = Flashcard::query();

        if ($from === 'en' && $to === 'sw') {
            $flashcard = $query->whereRaw('LOWER(english_meaning) = ?', [$text])->first();
            if ($flashcard) {
                return [
                    'translation' => $flashcard->swahili_word,
                    'pronunciation' => $flashcard->pronunciation,
                    'source' => 'flashcard',
                    'tokens_used' => null,
                ];
            }
        }

        if ($from === 'sw' && $to === 'en') {
            $flashcard = $query->whereRaw('LOWER(swahili_word) = ?', [$text])->first();
            if ($flashcard) {
                return [
                    'translation' => $flashcard->english_meaning,
                    'pronunciation' => $flashcard->pronunciation,
                    'source' => 'flashcard',
                    'tokens_used' => null,
                ];
            }
        }

        return null;
    }

    private function lookupCachedTranslation(string $normalized, string $from, string $to): ?array
    {
        $cacheKey = 'translation.'.hash('sha256', $normalized.'|'.$from.'|'.$to);

        $cached = Cache::get($cacheKey);
        if (is_array($cached) && isset($cached['translation'])) {
            return [
                'translation' => $cached['translation'],
                'pronunciation' => $cached['pronunciation'] ?? null,
                'source' => 'cache',
                'tokens_used' => null,
            ];
        }

        $log = TranslationLog::query()
            ->whereRaw('LOWER(original_text) = ?', [$normalized])
            ->where('from_lang', $from)
            ->where('to_lang', $to)
            ->whereNotNull('translated_text')
            ->where(function ($q) {
                $q->whereNull('source')
                    ->orWhereNotIn('source', ['fallback']);
            })
            ->latest('id')
            ->first();

        if (! $log || str_starts_with($log->translated_text, '[No translation found]')) {
            return null;
        }

        $result = [
            'translation' => $log->translated_text,
            'pronunciation' => null,
            'source' => 'cache',
            'tokens_used' => null,
        ];

        Cache::put($cacheKey, $result, now()->addDay());

        return $result;
    }

    private function translateWithOpenAi(string $text, string $from, string $to, ?User $user = null): array
    {
        if (! $this->languageService->isAvailable()) {
            return [
                'translation' => "[No translation found] Please try a common word or phrase. ({$from} → {$to})",
                'pronunciation' => null,
                'source' => 'fallback',
                'tokens_used' => null,
            ];
        }

        if ($user) {
            $this->quota->assertWithinQuota($user);
        }

        try {
            $ai = $this->languageService->translate($text, $from, $to);
            $result = [
                'translation' => $ai['content'],
                'pronunciation' => null,
                'source' => 'openai',
                'tokens_used' => $ai['tokens_used'],
            ];

            if ($user) {
                $this->quota->consume($user, 'translate', $ai['tokens_used']);
            }

            $cacheKey = 'translation.'.hash('sha256', mb_strtolower(trim($text)).'|'.$from.'|'.$to);
            Cache::put($cacheKey, $result, now()->addDay());

            return $result;
        } catch (AiServiceException $e) {
            Log::warning('OpenAI translation failed', ['message' => $e->getMessage()]);
            throw $e;
        }
    }

    private function logTranslation(?User $user, string $text, array $result, string $from, string $to): void
    {
        // Skip logging pure cache hits — already stored; saves a write on every repeat phrase
        if (in_array($result['source'] ?? null, ['fallback', 'cache'], true)) {
            return;
        }

        TranslationLog::create([
            'user_id' => $user?->id,
            'original_text' => $text,
            'translated_text' => $result['translation'],
            'from_lang' => $from,
            'to_lang' => $to,
            'source' => $result['source'] ?? null,
            'mode' => 'translate',
            'tokens_used' => $result['tokens_used'] ?? null,
            'is_favorite' => false,
        ]);
    }
}
