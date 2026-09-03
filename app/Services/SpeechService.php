<?php

/** Hii service ina mantiki ya biashara inayotumiwa na sehemu hii ya mfumo. */

namespace App\Services;

use App\Exceptions\AiServiceException;
use App\Services\Ai\ElevenLabsClient;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SpeechService
{
    public function __construct(private ElevenLabsClient $elevenLabs) {}

    public function getSupportedLanguages(): array
    {
        return [
            'en' => ['code' => 'en-US', 'name' => 'English', 'label' => 'English'],
            'sw' => ['code' => 'sw-KE', 'name' => 'Swahili', 'label' => 'Kiswahili'],
        ];
    }

    public function getBrowserSupportNotice(): string
    {
        return 'Translation uses OpenAI. Listen uses ElevenLabs when a voice is configured in Admin → AI Settings.';
    }

    public function getSpeechConfig(): array
    {
        return [
            'languages' => $this->getSupportedLanguages(),
            'notice' => $this->getBrowserSupportNotice(),
            'stt_supported' => app(\App\Services\Ai\OpenAiClient::class)->isConfigured(),
            'tts_supported' => $this->elevenLabs->isConfigured() && filled($this->resolveVoiceId()),
            'cloud_tts' => $this->elevenLabs->isConfigured() && filled($this->resolveVoiceId()),
            'elevenlabs_available' => $this->elevenLabs->isConfigured() && filled($this->resolveVoiceId()),
        ];
    }

    /**
     * @return array{url: string|null, provider: string, message: string|null}
     */
    public function synthesize(string $text, string $lang = 'sw'): array
    {
        $text = trim($text);

        if ($text === '') {
            throw new AiServiceException('Nothing to speak.');
        }

        if (mb_strlen($text) > 1000) {
            $text = mb_substr($text, 0, 1000);
        }

        if (! $this->elevenLabs->isConfigured()) {
            return [
                'url' => null,
                'provider' => 'browser',
                'message' => 'ElevenLabs is not configured; use browser playback.',
            ];
        }

        $voiceId = $this->resolveVoiceId();

        if (! $voiceId) {
            return [
                'url' => null,
                'provider' => 'browser',
                'message' => 'No cloned voice configured yet; use browser playback.',
            ];
        }

        try {
            $audio = $this->elevenLabs->synthesize($text, $voiceId);
            $filename = 'tts/'.Str::uuid().'.mp3';
            Storage::disk('public')->put($filename, $audio);

            return [
                'url' => asset('storage/'.$filename),
                'provider' => 'elevenlabs',
                'message' => null,
            ];
        } catch (AiServiceException $e) {
            Log::warning('TTS synthesize failed', ['error' => $e->getMessage(), 'lang' => $lang]);

            return [
                'url' => null,
                'provider' => 'browser',
                'message' => $e->toUserMessage(),
            ];
        }
    }

    /**
     * @param  array<int, UploadedFile>  $samples
     */
    public function cloneOwnerVoice(array $samples, string $name = 'Swahili LMS Owner'): string
    {
        $voiceId = $this->elevenLabs->cloneVoice(
            $name,
            $samples,
            'Owner voice for Swahili Learning LMS text-to-speech.'
        );

        return $voiceId;
    }

    public function resolveVoiceId(): ?string
    {
        $fromEnv = config('services.elevenlabs.voice_id');

        return filled($fromEnv) ? (string) $fromEnv : null;
    }

    public function currentVoiceId(): ?string
    {
        return $this->resolveVoiceId();
    }
}
