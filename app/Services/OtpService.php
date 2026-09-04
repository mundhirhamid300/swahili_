<?php

/** Hii service ina mantiki ya biashara inayotumiwa na sehemu hii ya mfumo. */

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class OtpService
{
    public function send(string $purpose, string $email): void
    {
        $rateKey = "otp-send:{$purpose}:".strtolower($email);
        if (RateLimiter::tooManyAttempts($rateKey, 3)) {
            throw ValidationException::withMessages(['otp' => 'Too many OTP requests. Try again in '.RateLimiter::availableIn($rateKey).' seconds.']);
        }

        RateLimiter::hit($rateKey, 300);
        $otp = (string) random_int(100000, 999999);
        Cache::put($this->key($purpose, $email), [
            'hash' => Hash::make($otp),
            'attempts' => 0,
        ], now()->addMinutes(10));

        try {
            if (filled(config('services.brevo.key'))) {
                $this->sendThroughBrevoApi($email, $purpose, $otp);
            } else {
                Mail::raw("Your Swahili Learning verification code is: {$otp}\n\nThis code expires in 10 minutes. Never share it with anyone.", function ($message) use ($email, $purpose) {
                    $message->to($email)->subject('Swahili Learning '.ucfirst($purpose).' OTP');
                });
            }
        } catch (\Throwable $exception) {
            Cache::forget($this->key($purpose, $email));
            report($exception);

            throw ValidationException::withMessages([
                'email' => 'We could not send the security code. Please try again later or contact the administrator.',
            ]);
        }
    }

    public function verify(string $purpose, string $email, string $otp): bool
    {
        $key = $this->key($purpose, $email);
        $record = Cache::get($key);
        if (! $record || ($record['attempts'] ?? 0) >= 5) return false;

        if (! Hash::check($otp, $record['hash'])) {
            $record['attempts'] = ($record['attempts'] ?? 0) + 1;
            Cache::put($key, $record, now()->addMinutes(10));
            return false;
        }

        Cache::forget($key);
        return true;
    }

    private function key(string $purpose, string $email): string
    {
        return 'otp:'.$purpose.':'.hash('sha256', strtolower($email));
    }

    private function sendThroughBrevoApi(string $email, string $purpose, string $otp): void
    {
        $response = Http::acceptJson()
            ->timeout(20)
            ->withHeaders(['api-key' => config('services.brevo.key')])
            ->post('https://api.brevo.com/v3/smtp/email', [
                'sender' => [
                    'name' => config('mail.from.name'),
                    'email' => config('mail.from.address'),
                ],
                'to' => [['email' => $email]],
                'subject' => 'Swahili Learning '.ucfirst($purpose).' OTP',
                'textContent' => "Your Swahili Learning verification code is: {$otp}\n\nThis code expires in 10 minutes. Never share it with anyone.",
            ]);

        if ($response->successful()) {
            return;
        }

        Log::error('Brevo OTP API request failed', ['status' => $response->status(), 'body' => $response->json() ?? $response->body()]);
        throw new \RuntimeException('Brevo could not send the OTP.');
    }
}
