<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('learning_streak')->default(0)->after('learning_level');
            $table->date('last_learning_at')->nullable()->after('learning_streak');
            $table->unsignedTinyInteger('weekly_goal')->default(5)->after('last_learning_at');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->string('topic')->nullable()->after('level');
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('audio_path');
            $table->unsignedTinyInteger('quiz_pass_mark')->default(70)->after('status');
            $table->unsignedSmallInteger('quiz_time_limit')->nullable()->after('quiz_pass_mark');
            $table->boolean('quiz_allow_retake')->default(false)->after('quiz_time_limit');
            $table->unsignedTinyInteger('quiz_max_attempts')->default(1)->after('quiz_allow_retake');
        });

        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('attempt_number')->default(1);
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedTinyInteger('score')->default(0);
            $table->boolean('passed')->default(false);
            $table->unsignedInteger('time_spent_seconds')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'lesson_id']);
        });

        Schema::table('quiz_answers', function (Blueprint $table) {
            $table->dropForeign(['quiz_id']);
            $table->dropForeign(['user_id']);
            $table->dropUnique(['quiz_id', 'user_id']);
        });

        Schema::table('quiz_answers', function (Blueprint $table) {
            $table->foreignId('quiz_attempt_id')->nullable()->after('id')->constrained('quiz_attempts')->nullOnDelete();
            $table->foreign('quiz_id')->references('id')->on('quizzes')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['quiz_attempt_id', 'quiz_id']);
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50);
            $table->string('title');
            $table->text('message');
            $table->string('link')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('notifications');

        Schema::table('quiz_answers', function (Blueprint $table) {
            $table->dropUnique(['quiz_attempt_id', 'quiz_id']);
            $table->dropForeign(['quiz_attempt_id']);
            $table->dropColumn('quiz_attempt_id');
            $table->dropForeign(['quiz_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::table('quiz_answers', function (Blueprint $table) {
            $table->foreign('quiz_id')->references('id')->on('quizzes')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['quiz_id', 'user_id']);
        });

        Schema::dropIfExists('quiz_attempts');

        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn([
                'image_path',
                'quiz_pass_mark',
                'quiz_time_limit',
                'quiz_allow_retake',
                'quiz_max_attempts',
            ]);
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('topic');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['learning_streak', 'last_learning_at', 'weekly_goal']);
        });
    }
};
