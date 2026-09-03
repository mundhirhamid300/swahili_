<?php

/** Hii service ina mantiki ya biashara inayotumiwa na sehemu hii ya mfumo. */

namespace App\Services\Ai;

use App\Exceptions\AiServiceException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiClient
{
    public function isConfigured(): bool
    {
        return filled(config('services.openai.key'));
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array{content: string, tokens_used: int|null}
     */
    public function chatCompletions(array $messages, ?int $maxTokens = null, float $temperature = 0.3): array
    {
        if (! $this->isConfigured()) {
            throw new AiServiceException('OpenAI is not configured. Add OPENAI_API_KEY to your environment.', 'openai');
        }

        $maxTokens ??= (int) config('services.openai.max_tokens', 250);

        try {
            $request = Http::withToken(config('services.openai.key'))
                ->acceptJson()
                ->connectTimeout(10)
                ->timeout((int) config('services.openai.timeout', 30))
                ->retry(2, 500);

            if ($caBundle = config('services.openai.ca_bundle')) {
                $request = $request->withOptions(['verify' => $caBundle]);
            }

            $response = $request->post(rtrim(config('services.openai.base_url'), '/').'/chat/completions', [
                    'model' => config('services.openai.model', 'gpt-4o-mini'),
                    'messages' => $messages,
                    'max_tokens' => $maxTokens,
                    'temperature' => $temperature,
                ]);
        } catch (\Throwable $e) {
            Log::warning('OpenAI request failed', ['error' => $e->getMessage()]);
            throw new AiServiceException('Unable to reach OpenAI. Please try again shortly.', 'openai', previous: $e);
        }

        if ($response->status() === 401) {
            throw new AiServiceException('OpenAI authentication failed. Check OPENAI_API_KEY.', 'openai', 401);
        }

        if ($response->status() === 429) {
            throw new AiServiceException('OpenAI rate limit reached. Please wait and try again.', 'openai', 429);
        }

        if ($response->failed()) {
            Log::warning('OpenAI API error', [
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ]);
            throw new AiServiceException('OpenAI could not process this request.', 'openai', $response->status());
        }

        $data = $response->json();
        $content = trim((string) data_get($data, 'choices.0.message.content', ''));

        if ($content === '') {
            throw new AiServiceException('OpenAI returned an empty response.', 'openai', $response->status());
        }

        return [
            'content' => $content,
            'tokens_used' => data_get($data, 'usage.total_tokens'),
        ];
    }

    /**
     * Transcribe audio with Whisper (speech-to-text).
     *
     * @return array{text: string, tokens_used: int|null}
     */
    public function transcribe(string $absolutePath, string $language = 'sw', ?string $filename = null): array
    {
        if (! $this->isConfigured()) {
            throw new AiServiceException('OpenAI is not configured. Add OPENAI_API_KEY to your environment.', 'openai');
        }

        if (! is_readable($absolutePath)) {
            throw new AiServiceException('Audio file could not be read for transcription.', 'openai');
        }

        $filename ??= basename($absolutePath);

        try {
            $request = Http::withToken(config('services.openai.key'))
                ->acceptJson()
                ->connectTimeout(10)
                ->timeout((int) config('services.openai.timeout', 30))
                ->retry(2, 500);

            if ($caBundle = config('services.openai.ca_bundle')) {
                $request = $request->withOptions(['verify' => $caBundle]);
            }

            $response = $request->attach('file', file_get_contents($absolutePath), $filename)
                ->post(rtrim(config('services.openai.base_url'), '/').'/audio/transcriptions', [
                    'model' => config('services.openai.whisper_model', 'whisper-1'),
                    'language' => $language,
                    'response_format' => 'json',
                ]);
        } catch (\Throwable $e) {
            Log::warning('OpenAI Whisper request failed', ['error' => $e->getMessage()]);
            throw new AiServiceException('Unable to reach OpenAI speech recognition. Please try again shortly.', 'openai', previous: $e);
        }

        if ($response->status() === 401) {
            throw new AiServiceException('OpenAI authentication failed. Check OPENAI_API_KEY.', 'openai', 401);
        }

        if ($response->status() === 429) {
            throw new AiServiceException('OpenAI rate limit reached. Please wait and try again.', 'openai', 429);
        }

        if ($response->failed()) {
            Log::warning('OpenAI Whisper API error', [
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ]);
            throw new AiServiceException('Could not transcribe your audio. Try a clearer, shorter recording.', 'openai', $response->status());
        }

        $text = trim((string) data_get($response->json(), 'text', ''));

        return [
            'text' => $text,
            'tokens_used' => null,
        ];
    }
}
