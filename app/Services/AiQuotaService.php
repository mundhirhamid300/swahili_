<?php

/** Hii service ina mantiki ya biashara inayotumiwa na sehemu hii ya mfumo. */

namespace App\Services;

use App\Exceptions\AiServiceException;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class AiQuotaService
{
    public function dailyLimit(): int
    {
        return (int) config('services.openai.daily_quota', 30);
    }

    public function remaining(User $user): int
    {
        return max(0, $this->dailyLimit() - (int) Cache::get($this->key($user), 0));
    }

    public function assertWithinQuota(User $user): void
    {
        if ($this->remaining($user) <= 0) {
            throw new AiServiceException(
                'You have reached today\'s AI tutor limit. Please continue tomorrow.',
                'quota'
            );
        }
    }

    public function consume(User $user, string $feature, ?int $tokens = null, string $provider = 'openai', array $meta = []): void
    {
        $key = $this->key($user);
        Cache::add($key, 0, now()->endOfDay());
        Cache::increment($key);
    }

    private function key(User $user): string
    {
        return 'ai-tutor:'.$user->id.':'.now()->toDateString();
    }
}
