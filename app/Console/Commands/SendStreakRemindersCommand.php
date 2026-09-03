<?php

/** Hii command huendesha kazi ya mfumo kupitia command line. */

namespace App\Console\Commands;

use App\Services\StreakReminderService;
use Illuminate\Console\Command;

class SendStreakRemindersCommand extends Command
{
    protected $signature = 'lms:streak-reminders';

    protected $description = 'Send in-app (and email) reminders to keep Swahili learning streaks';

    public function handle(StreakReminderService $service): int
    {
        $sent = $service->sendDueReminders();
        $this->info("Sent {$sent} streak reminder(s).");

        return self::SUCCESS;
    }
}
