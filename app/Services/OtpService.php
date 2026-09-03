<?php

/** Hii service ina mantiki ya biashara inayotumiwa na sehemu hii ya mfumo. */

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

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
            Mail::raw("Your Swahili Learning verification code is: {$otp}\n\nThis code expires in 10 minutes. Never share it with anyone.", function ($message) use ($email, $purpose) {
                $message->to($email)->subject('Swahili Learning '.ucfirst($purpose).' OTP');
            });
        } catch (TransportExceptionInterface $exception) {
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
}
