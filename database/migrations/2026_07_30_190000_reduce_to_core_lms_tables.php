<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'flashcard_reviews',
            'practice_drills',
            'pronunciation_attempts',
            'xp_events',
            'ai_usage_logs',
            'ai_daily_quotas',
            'ai_settings',
            'chatbot_sessions',
            'translations_log',
            'notifications',
            'certificates',
            'audit_logs',
            'personal_access_tokens',
            'failed_jobs',
            'jobs',
        ];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }
    }

    public function down(): void
    {
        // These optional feature tables are intentionally not recreated.
        // Their original migrations remain the source of truth if restored.
    }
};
