<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('xp')->default(0)->after('weekly_goal');
            $table->boolean('leaderboard_opt_in')->default(true)->after('xp');
            $table->boolean('streak_reminders')->default(true)->after('leaderboard_opt_in');
        });

        Schema::table('translations_log', function (Blueprint $table) {
            $table->boolean('is_favorite')->default(false)->after('tokens_used');
        });

        Schema::create('flashcard_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('flashcard_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('repetitions')->default(0);
            $table->unsignedSmallInteger('interval_days')->default(0);
            $table->decimal('ease_factor', 4, 2)->default(2.50);
            $table->timestamp('next_review_at')->nullable();
            $table->timestamp('last_reviewed_at')->nullable();
            $table->unsignedTinyInteger('last_quality')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'flashcard_id']);
            $table->index(['user_id', 'next_review_at']);
        });

        Schema::create('ai_daily_quotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('usage_date');
            $table->unsignedInteger('request_count')->default(0);
            $table->unsignedInteger('tokens_used')->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'usage_date']);
        });

        Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('feature', 60);
            $table->unsignedInteger('tokens_used')->nullable();
            $table->string('provider', 40)->default('openai');
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['feature', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('xp_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('source', 60);
            $table->unsignedSmallInteger('points');
            $table->string('meta')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
            $table->index('created_at');
        });

        Schema::create('practice_drills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->json('questions');
            $table->json('answers')->nullable();
            $table->unsignedTinyInteger('score')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('pronunciation_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained()->nullOnDelete();
            $table->string('phrase');
            $table->unsignedTinyInteger('score');
            $table->string('rating', 20)->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pronunciation_attempts');
        Schema::dropIfExists('practice_drills');
        Schema::dropIfExists('xp_events');
        Schema::dropIfExists('ai_usage_logs');
        Schema::dropIfExists('ai_daily_quotas');
        Schema::dropIfExists('flashcard_reviews');

        Schema::table('translations_log', function (Blueprint $table) {
            $table->dropColumn('is_favorite');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['xp', 'leaderboard_opt_in', 'streak_reminders']);
        });
    }
};
