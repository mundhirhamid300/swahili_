<?php

/** Hii service ina mantiki ya biashara inayotumiwa na sehemu hii ya mfumo. */

namespace App\Services\Ai;

use App\Exceptions\AiServiceException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ElevenLabsClient
{
    public function isConfigured(): bool
    {
        return filled(config('services.elevenlabs.key'));
    }

    public function synthesize(string $text, string $voiceId): string
    {
        if (! $this->isConfigured()) {
            throw new AiServiceException('ElevenLabs is not configured. Add ELEVENLABS_API_KEY to your environment.', 'elevenlabs');
        }

        if ($voiceId === '') {
            throw new AiServiceException('No ElevenLabs voice is configured. Clone a voice or set ELEVENLABS_VOICE_ID.', 'elevenlabs');
        }

        $modelId = config('services.elevenlabs.model_id', 'eleven_multilingual_v2');

        try {
            $response = Http::withHeaders([
                'xi-api-key' => config('services.elevenlabs.key'),
                'Accept' => 'audio/mpeg',
            ])
                ->timeout((int) config('services.elevenlabs.timeout', 60))
                ->post(
                    rtrim(config('services.elevenlabs.base_url'), '/').'/text-to-speech/'.$voiceId,
                    [
                        'text' => $text,
                        'model_id' => $modelId,
                        'voice_settings' => [
                            'stability' => 0.45,
                            'similarity_boost' => 0.75,
                        ],
                    ]
                );
        } catch (\Throwable $e) {
            Log::warning('ElevenLabs TTS failed', ['error' => $e->getMessage()]);
            throw new AiServiceException('Unable to reach ElevenLabs. Please try again shortly.', 'elevenlabs', previous: $e);
        }

        if ($response->status() === 401) {
            throw new AiServiceException('ElevenLabs authentication failed. Check ELEVENLABS_API_KEY.', 'elevenlabs', 401);
        }

        if ($response->status() === 429) {
            throw new AiServiceException('ElevenLabs rate limit reached. Please wait and try again.', 'elevenlabs', 429);
        }

        if ($response->failed()) {
            Log::warning('ElevenLabs TTS error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new AiServiceException('ElevenLabs could not synthesize speech.', 'elevenlabs', $response->status());
        }

        return $response->body();
    }

    /**
     * @param  array<int, UploadedFile>  $files
     */
    public function cloneVoice(string $name, array $files, ?string $description = null): string
    {
        if (! $this->isConfigured()) {
            throw new AiServiceException('ElevenLabs is not configured. Add ELEVENLABS_API_KEY to your environment.', 'elevenlabs');
        }

        if ($files === []) {
            throw new AiServiceException('At least one voice sample is required for cloning.', 'elevenlabs');
        }

        try {
            $pending = Http::withHeaders([
                'xi-api-key' => config('services.elevenlabs.key'),
            ])->timeout((int) config('services.elevenlabs.timeout', 120));

            foreach ($files as $index => $file) {
                $pending = $pending->attach(
                    'files',
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName() ?: "sample-{$index}.mp3"
                );
            }

            $payload = ['name' => $name];
            if ($description) {
                $payload['description'] = $description;
            }

            $response = $pending->post(
                rtrim(config('services.elevenlabs.base_url'), '/').'/voices/add',
                $payload
            );
        } catch (\Throwable $e) {
            Log::warning('ElevenLabs voice clone failed', ['error' => $e->getMessage()]);
            throw new AiServiceException('Unable to reach ElevenLabs for voice cloning.', 'elevenlabs', previous: $e);
        }

        if ($response->failed()) {
            Log::warning('ElevenLabs clone error', [
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ]);
            throw new AiServiceException('Voice cloning failed. Check sample quality and API quota.', 'elevenlabs', $response->status());
        }

        $voiceId = (string) data_get($response->json(), 'voice_id', '');

        if ($voiceId === '') {
            throw new AiServiceException('ElevenLabs did not return a voice id.', 'elevenlabs');
        }

        return $voiceId;
    }
}
