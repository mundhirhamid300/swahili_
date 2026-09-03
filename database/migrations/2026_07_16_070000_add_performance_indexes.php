<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('progress', function (Blueprint $table) {
            $table->index(['user_id', 'completed']);
            $table->index(['user_id', 'completed', 'updated_at']);
        });

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->index(['user_id', 'submitted_at']);
            $table->index(['lesson_id', 'submitted_at']);
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->index(['user_id', 'status']);
        });

        Schema::table('translations_log', function (Blueprint $table) {
            $table->index(['user_id', 'is_favorite']);
            $table->index(['from_lang', 'to_lang']);
        });

        Schema::table('chatbot_sessions', function (Blueprint $table) {
            $table->index(['user_id', 'mode', 'id']);
        });

        Schema::table('ai_usage_logs', function (Blueprint $table) {
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('progress', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'completed']);
            $table->dropIndex(['user_id', 'completed', 'updated_at']);
        });

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'submitted_at']);
            $table->dropIndex(['lesson_id', 'submitted_at']);
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status']);
        });

        Schema::table('translations_log', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_favorite']);
            $table->dropIndex(['from_lang', 'to_lang']);
        });

        Schema::table('chatbot_sessions', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'mode', 'id']);
        });

        Schema::table('ai_usage_logs', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });
    }
};
