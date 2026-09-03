<?php

/** Hii service ina mantiki ya biashara inayotumiwa na sehemu hii ya mfumo. */

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class StreakReminderService
{
    public function __construct(private NotificationService $notifications) {}

    public function sendDueReminders(): int
    {
        $sent = 0;
        $yesterday = Carbon::yesterday()->toDateString();

        User::query()
            ->where('role', 'student')
            ->where('status', 'active')
            ->where('streak_reminders', true)
            ->where('learning_streak', '>', 0)
            ->where(function ($q) {
                $q->whereNull('last_learning_at')
                    ->orWhereDate('last_learning_at', '<', Carbon::today());
            })
            ->whereDate('last_learning_at', $yesterday)
            ->orderBy('id')
            ->chunkById(50, function ($users) use (&$sent) {
                foreach ($users as $user) {
                    $this->notifications->notify(
                        $user,
                        'streak_reminder',
                        'Keep your streak!',
                        'You have a '.$user->learning_streak.'-day streak. Practice 5 minutes of Kiswahili today.',
                        route('student.dashboard')
                    );

                    try {
                        Mail::raw(
                            "Habari {$user->name}!\n\nYour Swahili streak is {$user->learning_streak} days. Keep it going with a short lesson today.\n\n".url('/student/dashboard'),
                            function ($message) use ($user) {
                                $message->to($user->email)->subject('Keep your Kiswahili streak');
                            }
                        );
                    } catch (\Throwable $e) {
                        Log::info('Streak email skipped/failed', ['user' => $user->id, 'error' => $e->getMessage()]);
                    }

                    $sent++;
                }
            });

        return $sent;
    }
}
