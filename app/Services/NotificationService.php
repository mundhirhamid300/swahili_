<?php

/** Hii service ina mantiki ya biashara inayotumiwa na sehemu hii ya mfumo. */

namespace App\Services;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class NotificationService
{
    public function notify(User $user, string $type, string $title, string $message, ?string $link = null): AppNotification
    {
        $notification = AppNotification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
        ]);

        Cache::forget("user.{$user->id}.header_notifications");

        return $notification;
    }

    public function unreadCount(User $user): int
    {
        return AppNotification::where('user_id', $user->id)->whereNull('read_at')->count();
    }

    public function recent(User $user, int $limit = 10)
    {
        return AppNotification::where('user_id', $user->id)
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function markAllRead(User $user): void
    {
        AppNotification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        Cache::forget("user.{$user->id}.header_notifications");
    }
}
