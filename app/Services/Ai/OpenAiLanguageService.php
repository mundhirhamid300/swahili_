<?php

/** Hii service ina mantiki ya biashara inayotumiwa na sehemu hii ya mfumo. */

namespace App\Services\Ai;

use App\Exceptions\AiServiceException;

class OpenAiLanguageService
{
    public function __construct(private OpenAiClient $client) {}

    public function isAvailable(): bool
    {
        return $this->client->isConfigured();
    }

    /**
     * @param  array<int, array{role:string, content:string}>  $history
     * @return array{content:string, details:array<string,string>, tokens_used:int|null}
     */
    public function swahiliLearningReply(string $message, array $history = []): array
    {
        $messages = [[
            'role' => 'system',
            'content' => <<<'PROMPT'
You are Mwalimu AI, a friendly tutor whose only purpose is helping foreigners learn Kiswahili.
The learner may write a word or short phrase in any language. Translate it into natural Kiswahili and teach it.
Return ONLY valid JSON with these string fields: swahili, meaning, pronunciation, example_swahili, example_meaning, note.
Write meaning and note in the learner's language when clear; otherwise use simple English.
Pronunciation must be a simple syllable guide, not IPA. Keep every field concise.
If the request is unrelated to learning Kiswahili, politely redirect in the note while keeping the other fields empty.
PROMPT,
        ]];

        foreach ($history as $turn) {
            $messages[] = $turn;
        }

        $messages[] = ['role' => 'user', 'content' => $message];
        $result = $this->client->chatCompletions(
            $messages,
            (int) config('services.openai.limits.tutor', 300),
            0.2
        );

        $raw = trim($result['content']);
        $raw = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $raw) ?: $raw;
        $decoded = json_decode($raw, true);
        $fields = ['swahili', 'meaning', 'pronunciation', 'example_swahili', 'example_meaning', 'note'];
        $details = [];

        foreach ($fields as $field) {
            $details[$field] = is_array($decoded) ? trim((string) ($decoded[$field] ?? '')) : '';
        }

        if (! is_array($decoded)) {
            $details['note'] = $result['content'];
        }

        $content = collect([
            $details['swahili'] ? 'Kiswahili: '.$details['swahili'] : null,
            $details['meaning'] ? 'Meaning: '.$details['meaning'] : null,
            $details['pronunciation'] ? 'Pronunciation: '.$details['pronunciation'] : null,
            $details['example_swahili'] ? 'Example: '.$details['example_swahili'] : null,
            $details['example_meaning'] ? 'Example meaning: '.$details['example_meaning'] : null,
            $details['note'] ?: null,
        ])->filter()->implode("\n");

        return ['content' => $content, 'details' => $details, 'tokens_used' => $result['tokens_used']];
    }

    /**
     * @return array{content: string, tokens_used: int|null}
     */
    public function translate(string $text, string $from, string $to): array
    {
        $fromName = $from === 'sw' ? 'Swahili' : 'English';
        $toName = $to === 'sw' ? 'Swahili' : 'English';

        return $this->client->chatCompletions([
            [
                'role' => 'system',
                'content' => 'You are a precise Swahili↔English translator for language learners. Reply with ONLY the translation. No quotes, labels, or explanation.',
            ],
            [
                'role' => 'user',
                'content' => "Translate from {$fromName} to {$toName}:\n{$text}",
            ],
        ], (int) config('services.openai.limits.translate', 150), 0.2);
    }

    /**
     * @return array{content: string, tokens_used: int|null}
     */
    public function correctGrammar(string $text, string $lang = 'sw'): array
    {
        $langName = $lang === 'sw' ? 'Swahili' : 'English';

        return $this->client->chatCompletions([
            [
                'role' => 'system',
                'content' => "You correct {$langName} for foreign learners. Reply briefly: 1) Corrected text 2) One short tip. No fluff.",
            ],
            [
                'role' => 'user',
                'content' => $text,
            ],
        ], (int) config('services.openai.limits.explain', 250), 0.2);
    }

    /**
     * @return array{content: string, tokens_used: int|null}
     */
    public function explainVocabulary(string $text): array
    {
        return $this->client->chatCompletions([
            [
                'role' => 'system',
                'content' => 'Explain Swahili/English vocabulary for foreign learners. Include meaning, simple example sentence in Swahili with English gloss, and pronunciation tip. Keep under 120 words.',
            ],
            [
                'role' => 'user',
                'content' => $text,
            ],
        ], (int) config('services.openai.limits.explain', 250), 0.3);
    }

    /**
     * @return array{content: string, tokens_used: int|null}
     */
    public function explainSentence(string $text): array
    {
        return $this->client->chatCompletions([
            [
                'role' => 'system',
                'content' => 'Explain a Swahili or English sentence for learners: meaning, word-by-word gloss of key words, and structure tip. Keep under 140 words.',
            ],
            [
                'role' => 'user',
                'content' => $text,
            ],
        ], (int) config('services.openai.limits.explain', 250), 0.3);
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $history
     * @return array{content: string, tokens_used: int|null}
     */
    public function tutorReply(string $message, array $history = []): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a friendly Swahili tutor for foreigners. Keep answers short (under 100 words). Mix English with simple Kiswahili examples. Correct gently when helpful. Do not invent long lessons unless asked.',
            ],
        ];

        foreach ($history as $turn) {
            $messages[] = [
                'role' => $turn['role'],
                'content' => $turn['content'],
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $message,
        ];

        return $this->client->chatCompletions(
            $messages,
            (int) config('services.openai.limits.tutor', 300),
            0.4
        );
    }

    /**
     * @throws AiServiceException
     */
    public function runMode(string $mode, string $text, string $from = 'en', string $to = 'sw'): array
    {
        return match ($mode) {
            'translate' => $this->translate($text, $from, $to),
            'grammar' => $this->correctGrammar($text, $from === 'en' ? 'en' : 'sw'),
            'vocabulary' => $this->explainVocabulary($text),
            'sentence' => $this->explainSentence($text),
            default => throw new AiServiceException('Unsupported AI mode.'),
        };
    }
}
