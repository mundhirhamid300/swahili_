<?php

/** Hii service ina mantiki ya biashara inayotumiwa na sehemu hii ya mfumo. */

namespace App\Services;

use App\Models\User;
use App\Models\XpEvent;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class XpService
{
    public function award(User $user, string $source, int $points, ?string $meta = null): void
    {
        if ($points <= 0) {
            return;
        }

        XpEvent::create([
            'user_id' => $user->id,
            'source' => $source,
            'points' => $points,
            'meta' => $meta,
        ]);

        $user->increment('xp', $points);
        Cache::forget('leaderboard.weekly');
        Cache::forget("student.{$user->id}.stats");
    }

    public function weeklyLeaderboard(int $limit = 20): Collection
    {
        return Cache::remember('leaderboard.weekly', 120, function () use ($limit) {
            $weekStart = Carbon::now()->startOfWeek();

            return User::query()
                ->where('role', 'student')
                ->where('status', 'active')
                ->where('leaderboard_opt_in', true)
                ->leftJoin('xp_events', function ($join) use ($weekStart) {
                    $join->on('users.id', '=', 'xp_events.user_id')
                        ->where('xp_events.created_at', '>=', $weekStart);
                })
                ->select([
                    'users.id',
                    'users.name',
                    'users.xp',
                    'users.learning_streak',
                    'users.country',
                    DB::raw('COALESCE(SUM(xp_events.points), 0) as weekly_xp'),
                ])
                ->groupBy('users.id', 'users.name', 'users.xp', 'users.learning_streak', 'users.country')
                ->orderByDesc('weekly_xp')
                ->orderByDesc('users.xp')
                ->limit($limit)
                ->get();
        });
    }
}
