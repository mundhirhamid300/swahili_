<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_records', function (Blueprint $table) {
            $table->id();
            $table->string('record_type', 30)->index();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('quiz_id')->nullable();
            $table->unsignedBigInteger('quiz_attempt_id')->nullable();
            $table->unsignedBigInteger('current_quiz_id')->nullable();
            $table->foreignId('admin_feedback_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('swahili_word')->nullable();
            $table->string('english_meaning')->nullable();
            $table->string('pronunciation')->nullable();
            $table->string('audio_path')->nullable();

            $table->text('question')->nullable();
            $table->string('option_a')->nullable();
            $table->string('option_b')->nullable();
            $table->string('option_c')->nullable();
            $table->string('option_d')->nullable();
            $table->string('correct_answer', 1)->nullable();
            $table->unsignedSmallInteger('time_limit_seconds')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->boolean('completed')->nullable();
            $table->unsignedTinyInteger('score')->default(0);
            $table->string('status', 30)->nullable();

            $table->unsignedTinyInteger('attempt_number')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('question_started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->boolean('passed')->nullable();
            $table->unsignedInteger('time_spent_seconds')->nullable();
            $table->text('admin_feedback')->nullable();
            $table->timestamp('admin_feedback_at')->nullable();

            $table->string('selected_answer', 1)->nullable();
            $table->boolean('is_correct')->nullable();
            $table->boolean('timed_out')->default(false);
            $table->timestamps();

            $table->index(['record_type', 'lesson_id']);
            $table->index(['record_type', 'user_id']);
            $table->index(['record_type', 'quiz_attempt_id']);
        });

        $quizMap = [];
        foreach (DB::table('quizzes')->orderBy('id')->get() as $row) {
            $quizMap[$row->id] = DB::table('learning_records')->insertGetId([
                'record_type' => 'quiz',
                'lesson_id' => $row->lesson_id,
                'question' => $row->question,
                'option_a' => $row->option_a,
                'option_b' => $row->option_b,
                'option_c' => $row->option_c,
                'option_d' => $row->option_d,
                'correct_answer' => $row->correct_answer,
                'time_limit_seconds' => $row->time_limit_seconds,
                'sort_order' => $row->sort_order,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }

        foreach (DB::table('flashcards')->orderBy('id')->get() as $row) {
            DB::table('learning_records')->insert([
                'record_type' => 'flashcard',
                'lesson_id' => $row->lesson_id,
                'swahili_word' => $row->swahili_word,
                'english_meaning' => $row->english_meaning,
                'pronunciation' => $row->pronunciation,
                'audio_path' => $row->audio_path,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }

        foreach (DB::table('progress')->orderBy('id')->get() as $row) {
            DB::table('learning_records')->insert([
                'record_type' => 'progress',
                'user_id' => $row->user_id,
                'lesson_id' => $row->lesson_id,
                'completed' => $row->completed,
                'score' => $row->score,
                'status' => $row->status,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }

        $attemptMap = [];
        foreach (DB::table('quiz_attempts')->orderBy('id')->get() as $row) {
            $attemptMap[$row->id] = DB::table('learning_records')->insertGetId([
                'record_type' => 'quizattempt',
                'user_id' => $row->user_id,
                'lesson_id' => $row->lesson_id,
                'current_quiz_id' => $row->current_quiz_id ? ($quizMap[$row->current_quiz_id] ?? null) : null,
                'attempt_number' => $row->attempt_number,
                'started_at' => $row->started_at,
                'question_started_at' => $row->question_started_at,
                'submitted_at' => $row->submitted_at,
                'score' => $row->score,
                'passed' => $row->passed,
                'time_spent_seconds' => $row->time_spent_seconds,
                'admin_feedback' => $row->teacher_feedback,
                'admin_feedback_at' => $row->teacher_feedback_at,
                'admin_feedback_by' => $row->teacher_feedback_by,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }

        foreach (DB::table('quiz_answers')->orderBy('id')->get() as $row) {
            $newQuizId = $quizMap[$row->quiz_id] ?? null;
            $quiz = $newQuizId ? DB::table('learning_records')->where('id', $newQuizId)->first() : null;

            DB::table('learning_records')->insert([
                'record_type' => 'quizanswer',
                'user_id' => $row->user_id,
                'lesson_id' => $quiz?->lesson_id,
                'quiz_id' => $newQuizId,
                'quiz_attempt_id' => $row->quiz_attempt_id ? ($attemptMap[$row->quiz_attempt_id] ?? null) : null,
                'selected_answer' => $row->selected_answer,
                'is_correct' => $row->is_correct,
                'score' => $row->score,
                'timed_out' => $row->timed_out,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }

        Schema::dropIfExists('quiz_answers');
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('progress');
        Schema::dropIfExists('flashcards');
        Schema::dropIfExists('quizzes');
        Schema::dropIfExists('password_reset_tokens');
    }

    public function down(): void
    {
        // Consolidation is intentionally one-way because IDs are remapped.
    }
};
