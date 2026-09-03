<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('level', ['beginner', 'intermediate', 'advanced'])->default('beginner');
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->string('thumbnail')->nullable();
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->longText('content')->nullable();
            $table->string('audio_path')->nullable();
            $table->unsignedInteger('lesson_order')->default(1);
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamps();
        });

        Schema::create('flashcards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->string('swahili_word');
            $table->string('english_meaning');
            $table->string('pronunciation')->nullable();
            $table->string('audio_path')->nullable();
            $table->timestamps();
        });

        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->text('question');
            $table->string('option_a');
            $table->string('option_b');
            $table->string('option_c');
            $table->string('option_d');
            $table->enum('correct_answer', ['a', 'b', 'c', 'd']);
            $table->timestamps();
        });

        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['active', 'completed', 'dropped'])->default('active');
            $table->timestamp('enrolled_at')->useCurrent();
            $table->timestamps();
            $table->unique(['user_id', 'course_id']);
        });

        Schema::create('progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->boolean('completed')->default(false);
            $table->unsignedTinyInteger('score')->default(0);
            $table->enum('status', ['in_progress', 'completed'])->default('in_progress');
            $table->timestamps();
            $table->unique(['user_id', 'lesson_id']);
        });

        Schema::create('quiz_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('selected_answer', ['a', 'b', 'c', 'd']);
            $table->boolean('is_correct')->default(false);
            $table->unsignedTinyInteger('score')->default(0);
            $table->timestamps();
            $table->unique(['quiz_id', 'user_id']);
        });

        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('certificate_code')->unique();
            $table->timestamp('issued_at')->useCurrent();
            $table->boolean('revoked')->default(false);
            $table->timestamps();
            $table->unique(['user_id', 'course_id']);
        });

        Schema::create('chatbot_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('message');
            $table->text('response');
            $table->timestamps();
        });

        Schema::create('translations_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('original_text');
            $table->text('translated_text');
            $table->string('from_lang', 5);
            $table->string('to_lang', 5);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translations_log');
        Schema::dropIfExists('chatbot_sessions');
        Schema::dropIfExists('certificates');
        Schema::dropIfExists('quiz_answers');
        Schema::dropIfExists('progress');
        Schema::dropIfExists('enrollments');
        Schema::dropIfExists('quizzes');
        Schema::dropIfExists('flashcards');
        Schema::dropIfExists('lessons');
        Schema::dropIfExists('courses');
    }
};
