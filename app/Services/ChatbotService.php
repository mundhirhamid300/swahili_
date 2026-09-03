<?php

/** Hii service ina mantiki ya biashara inayotumiwa na sehemu hii ya mfumo. */

namespace App\Services;

use App\Exceptions\AiServiceException;
use App\Models\User;
use App\Services\Ai\OpenAiLanguageService;
use Illuminate\Support\Str;

class ChatbotService
{
    public function __construct(
        private OpenAiLanguageService $languageService,
        private AiQuotaService $quota
    ) {}

    /**
     * @return array{response:string, details:array<string,string>, conversation_id:string}
     */
    public function respond(User $user, string $message, string $mode = 'tutor', ?int $lessonId = null, ?string $conversationId = null): array
    {
        $this->quota->assertWithinQuota($user);
        $history = collect(session('swahili_ai_tutor.messages', []))
            ->take(-6)
            ->flatMap(fn (array $turn) => [
                ['role' => 'user', 'content' => Str::limit($turn['message'] ?? '', 300)],
                ['role' => 'assistant', 'content' => Str::limit($turn['response'] ?? '', 500)],
            ])->values()->all();

        try {
            $result = $this->languageService->swahiliLearningReply($message, $history);
        } catch (AiServiceException $exception) {
            if ($exception->provider !== 'openai') {
                throw $exception;
            }

            $result = $this->offlineReply($message);
        }
        $conversationId = $conversationId && Str::isUuid($conversationId)
            ? $conversationId
            : (session('swahili_ai_tutor.id') ?: (string) Str::uuid());

        $turns = session('swahili_ai_tutor.messages', []);
        $turns[] = [
            'message' => $message,
            'response' => $result['content'],
            'details' => $result['details'],
        ];

        session([
            'swahili_ai_tutor.id' => $conversationId,
            'swahili_ai_tutor.messages' => array_slice($turns, -20),
        ]);

        if (! ($result['offline'] ?? false)) {
            $this->quota->consume($user, 'swahili_tutor', $result['tokens_used']);
        }

        return [
            'response' => $result['content'],
            'details' => $result['details'],
            'conversation_id' => $conversationId,
        ];
    }

    public function messages(): array
    {
        return session('swahili_ai_tutor.messages', []);
    }

    public function clear(): void
    {
        session()->forget('swahili_ai_tutor');
    }

    /**
     * Keep the tutor useful when the external AI provider is temporarily offline.
     *
     * @return array{content:string, details:array<string,string>, tokens_used:null, offline:true}
     */
    private function offlineReply(string $message): array
    {
        $normalized = mb_strtolower(trim($message));
        $normalized = trim((string) preg_replace('/[^\pL\pN\s]+/u', '', $normalized));
        $dictionary = config('swahili.dictionary', []);
        $entry = $dictionary[$normalized] ?? null;

        if (is_array($entry)) {
            $swahili = (string) ($entry['sw'] ?? '');
            $meaning = (string) ($entry['en'] ?? $message);
            $pronunciation = (string) ($entry['pronunciation'] ?? '');
            $details = [
                'swahili' => $swahili,
                'meaning' => $meaning,
                'pronunciation' => $pronunciation,
                'example_swahili' => '',
                'example_meaning' => '',
                'note' => 'Jibu hili limetoka kwenye kamusi ya ndani kwa sababu AI haipatikani kwa muda.',
            ];

            return [
                'content' => "Kiswahili: {$swahili}\nMeaning: {$meaning}\nPronunciation: {$pronunciation}",
                'details' => $details,
                'tokens_used' => null,
                'offline' => true,
            ];
        }

        $content = 'Mwalimu AI hapatikani kwa muda, lakini bado unaweza kujaribu neno la kawaida kama hello, thank you, water, food, au good morning.';

        return [
            'content' => $content,
            'details' => [
                'swahili' => '',
                'meaning' => '',
                'pronunciation' => '',
                'example_swahili' => '',
                'example_meaning' => '',
                'note' => '',
            ],
            'tokens_used' => null,
            'offline' => true,
        ];
    }
}
