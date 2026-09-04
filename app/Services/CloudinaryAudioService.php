<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/** Uploads and deletes the course-word recordings stored in Cloudinary. */
class CloudinaryAudioService
{
    public function upload(UploadedFile $file): array
    {
        $config = $this->config();
        $timestamp = time();
        $publicId = 'word-'.Str::uuid();
        $params = ['folder' => 'swahili-learning/course-words', 'public_id' => $publicId, 'timestamp' => $timestamp];
        $stream = fopen($file->getRealPath(), 'r');

        try {
            $response = Http::timeout(60)->attach('file', $stream, $file->getClientOriginalName())
                ->post($this->endpoint($config['cloud_name'], 'upload'), $params + [
                    'api_key' => $config['api_key'],
                    'signature' => $this->signature($params, $config['api_secret']),
                ]);
        } finally {
            if (is_resource($stream)) fclose($stream);
        }

        if ($response->failed() || ! $response->json('secure_url')) {
            report(new RuntimeException('Cloudinary audio upload failed: '.$response->body()));
            throw new RuntimeException('Audio could not be saved online. Please try again.');
        }

        return ['url' => $response->json('secure_url'), 'public_id' => $response->json('public_id')];
    }

    public function delete(?string $publicId): void
    {
        if (! $publicId || ! $this->isConfigured()) return;

        $config = $this->config();
        $params = ['invalidate' => true, 'public_id' => $publicId, 'timestamp' => time()];

        try {
            Http::timeout(30)->post($this->endpoint($config['cloud_name'], 'destroy'), $params + [
                'api_key' => $config['api_key'],
                'signature' => $this->signature($params, $config['api_secret']),
            ]);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    private function config(): array
    {
        if (! $this->isConfigured()) throw new RuntimeException('Cloudinary credentials have not been configured.');
        return config('services.cloudinary');
    }

    private function isConfigured(): bool
    {
        return filled(config('services.cloudinary.cloud_name'))
            && filled(config('services.cloudinary.api_key'))
            && filled(config('services.cloudinary.api_secret'));
    }

    private function endpoint(string $cloudName, string $action): string
    {
        return "https://api.cloudinary.com/v1_1/{$cloudName}/video/{$action}";
    }

    private function signature(array $params, string $secret): string
    {
        ksort($params);
        return sha1(collect($params)->map(fn ($value, $key) => $key.'='.$value)->implode('&').$secret);
    }
}
