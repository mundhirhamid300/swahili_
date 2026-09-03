<?php

/** Hii service ina mantiki ya biashara inayotumiwa na sehemu hii ya mfumo. */

namespace App\Services;

use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class QuizService
{
    public function __construct(private ProgressService $progressService) {}

    public function orderedQuizzes(Lesson $lesson): Collection
    {
        return Quiz::where('lesson_id', $lesson->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function getOrStartAttempt(User $user, Lesson $lesson): QuizAttempt
    {
        $open = QuizAttempt::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->whereNull('submitted_at')
            ->latest('id')
            ->first();

        if ($open) {
            $this->syncOpenAttempt($open, $lesson);

            return $open->fresh(['answers']);
        }

        $attempts = QuizAttempt::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->count();

        if ($attempts > 0 && ! $lesson->quiz_allow_retake) {
            throw ValidationException::withMessages([
                'quiz' => 'Retakes are not allowed for this lesson quiz.',
            ]);
        }

        if ($attempts >= $lesson->quiz_max_attempts) {
            throw ValidationException::withMessages([
                'quiz' => 'You have reached the maximum number of quiz attempts.',
            ]);
        }

        $attempt = QuizAttempt::create([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'attempt_number' => $attempts + 1,
            'started_at' => now(),
        ]);

        $this->syncOpenAttempt($attempt, $lesson);

        return $attempt->fresh(['answers']);
    }

    public function canStartNewAttempt(User $user, Lesson $lesson): bool
    {
        $attempts = QuizAttempt::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->count();

        $hasOpen = QuizAttempt::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->whereNull('submitted_at')
            ->exists();

        if ($hasOpen) {
            return false;
        }

        if ($attempts === 0) {
            return true;
        }

        return $lesson->quiz_allow_retake && $attempts < $lesson->quiz_max_attempts;
    }

    /**
     * Apply overall + per-question timeouts, then ensure the current question timer is running.
     */
    public function syncOpenAttempt(QuizAttempt $attempt, Lesson $lesson): void
    {
        if ($attempt->submitted_at) {
            return;
        }

        $this->assertOverallNotExpired($attempt, $lesson, autoFinalize: true);

        // Keep expiring timed-out questions until we land on an open one or finish.
        for ($i = 0; $i < 50; $i++) {
            $current = $this->currentQuestion($attempt, $lesson);
            if (! $current) {
                $this->finalizeAttempt($attempt->user, $lesson, $attempt);

                return;
            }

            $this->beginQuestion($attempt, $current);

            if (! $this->isQuestionExpired($attempt, $current)) {
                return;
            }

            $this->recordTimeoutAnswer($attempt, $current);
            $attempt->refresh();
        }
    }

    public function currentQuestion(QuizAttempt $attempt, Lesson $lesson): ?Quiz
    {
        $answeredIds = QuizAnswer::where('quiz_attempt_id', $attempt->id)->pluck('quiz_id');

        return $this->orderedQuizzes($lesson)
            ->first(fn (Quiz $quiz) => ! $answeredIds->contains($quiz->id));
    }

    public function beginQuestion(QuizAttempt $attempt, Quiz $quiz): void
    {
        if ($attempt->current_quiz_id === $quiz->id && $attempt->question_started_at) {
            return;
        }

        $attempt->update([
            'current_quiz_id' => $quiz->id,
            'question_started_at' => now(),
        ]);
    }

    public function isQuestionExpired(QuizAttempt $attempt, Quiz $quiz): bool
    {
        $seconds = (int) ($quiz->time_limit_seconds ?: 45);
        if ($seconds <= 0 || ! $attempt->question_started_at || $attempt->current_quiz_id !== $quiz->id) {
            return false;
        }

        return now()->greaterThan(
            $attempt->question_started_at->copy()->addSeconds($seconds)
        );
    }

    public function questionRemainingSeconds(QuizAttempt $attempt, Quiz $quiz): int
    {
        $seconds = (int) ($quiz->time_limit_seconds ?: 45);
        if (! $attempt->question_started_at || $attempt->current_quiz_id !== $quiz->id) {
            return $seconds;
        }

        $deadline = $attempt->question_started_at->copy()->addSeconds($seconds);

        return max(0, $deadline->getTimestamp() - now()->getTimestamp());
    }

    public function recordTimeoutAnswer(QuizAttempt $attempt, Quiz $quiz): QuizAnswer
    {
        $existing = QuizAnswer::where('quiz_attempt_id', $attempt->id)
            ->where('quiz_id', $quiz->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        $wrong = collect(['a', 'b', 'c', 'd'])->first(fn ($opt) => $opt !== $quiz->correct_answer) ?? 'a';

        $answer = QuizAnswer::create([
            'quiz_attempt_id' => $attempt->id,
            'quiz_id' => $quiz->id,
            'user_id' => $attempt->user_id,
            'selected_answer' => $wrong,
            'is_correct' => false,
            'score' => 0,
            'timed_out' => true,
        ]);

        $attempt->update([
            'current_quiz_id' => null,
            'question_started_at' => null,
        ]);

        $this->maybeFinalizeAttempt(
            $attempt->user,
            Lesson::findOrFail($attempt->lesson_id),
            $attempt
        );

        return $answer;
    }

    public function submitAnswer(User $user, Lesson $lesson, Quiz $quiz, string $selectedAnswer): QuizAnswer
    {
        $attempt = $this->getOrStartAttempt($user, $lesson);
        $this->syncOpenAttempt($attempt, $lesson);
        $attempt = $attempt->fresh();

        if ($attempt->submitted_at) {
            throw ValidationException::withMessages([
                'quiz' => 'This quiz attempt is already finished.',
            ]);
        }

        $current = $this->currentQuestion($attempt, $lesson);
        if (! $current || $current->id !== $quiz->id) {
            throw ValidationException::withMessages([
                'quiz' => 'Please answer the current question before moving on.',
            ]);
        }

        if ($this->isQuestionExpired($attempt, $quiz)) {
            $this->recordTimeoutAnswer($attempt, $quiz);
            throw ValidationException::withMessages([
                'quiz' => 'Time ran out for this question. Moving to the next one.',
            ]);
        }

        if (QuizAnswer::where('quiz_attempt_id', $attempt->id)->where('quiz_id', $quiz->id)->exists()) {
            throw ValidationException::withMessages([
                'quiz' => 'You have already answered this question in the current attempt.',
            ]);
        }

        $isCorrect = $selectedAnswer === $quiz->correct_answer;

        $answer = QuizAnswer::create([
            'quiz_attempt_id' => $attempt->id,
            'quiz_id' => $quiz->id,
            'user_id' => $user->id,
            'selected_answer' => $selectedAnswer,
            'is_correct' => $isCorrect,
            'score' => $isCorrect ? 100 : 0,
            'timed_out' => false,
        ]);

        $attempt->update([
            'current_quiz_id' => null,
            'question_started_at' => null,
        ]);

        $this->maybeFinalizeAttempt($user, $lesson, $attempt);

        return $answer;
    }

    public function maybeFinalizeAttempt(User $user, Lesson $lesson, QuizAttempt $attempt): ?QuizAttempt
    {
        $totalQuizzes = Quiz::where('lesson_id', $lesson->id)->count();
        if ($totalQuizzes === 0) {
            return null;
        }

        $answered = QuizAnswer::where('quiz_attempt_id', $attempt->id)->count();
        if ($answered < $totalQuizzes) {
            return null;
        }

        return $this->finalizeAttempt($user, $lesson, $attempt);
    }

    public function finalizeAttempt(User $user, Lesson $lesson, QuizAttempt $attempt): QuizAttempt
    {
        if ($attempt->submitted_at) {
            return $attempt;
        }

        // Auto-mark any unanswered questions as timed out before scoring.
        foreach ($this->orderedQuizzes($lesson) as $quiz) {
            $exists = QuizAnswer::where('quiz_attempt_id', $attempt->id)
                ->where('quiz_id', $quiz->id)
                ->exists();
            if (! $exists) {
                $wrong = collect(['a', 'b', 'c', 'd'])->first(fn ($opt) => $opt !== $quiz->correct_answer) ?? 'a';
                QuizAnswer::create([
                    'quiz_attempt_id' => $attempt->id,
                    'quiz_id' => $quiz->id,
                    'user_id' => $user->id,
                    'selected_answer' => $wrong,
                    'is_correct' => false,
                    'score' => 0,
                    'timed_out' => true,
                ]);
            }
        }

        $avgScore = (int) QuizAnswer::where('quiz_attempt_id', $attempt->id)->avg('score');
        $passed = $avgScore >= $lesson->quiz_pass_mark;
        $timeSpent = max(0, now()->diffInSeconds($attempt->started_at));

        $attempt->update([
            'submitted_at' => now(),
            'score' => $avgScore,
            'passed' => $passed,
            'time_spent_seconds' => $timeSpent,
            'current_quiz_id' => null,
            'question_started_at' => null,
        ]);

        $this->progressService->recordLearningActivity($user);

        if ($passed) {
            $this->progressService->markLessonComplete($user, $lesson, $avgScore);
        } else {
            $this->progressService->markLessonInProgress($user, $lesson, $avgScore);
        }

        return $attempt->fresh();
    }

    public function assertNotExpired(QuizAttempt $attempt, Lesson $lesson): void
    {
        $this->assertOverallNotExpired($attempt, $lesson, autoFinalize: true);
    }

    public function assertOverallNotExpired(QuizAttempt $attempt, Lesson $lesson, bool $autoFinalize = false): void
    {
        if (! $lesson->quiz_time_limit || $attempt->submitted_at) {
            return;
        }

        $deadline = $attempt->started_at->copy()->addMinutes($lesson->quiz_time_limit);
        if (now()->greaterThan($deadline)) {
            if ($autoFinalize) {
                $this->finalizeAttempt($attempt->user, $lesson, $attempt);
            }

            throw ValidationException::withMessages([
                'quiz' => 'Overall quiz time is up. Your attempt has been submitted automatically.',
            ]);
        }
    }

    public function remainingSeconds(QuizAttempt $attempt, Lesson $lesson): ?int
    {
        if (! $lesson->quiz_time_limit || $attempt->submitted_at) {
            return null;
        }

        $deadline = $attempt->started_at->copy()->addMinutes($lesson->quiz_time_limit);

        return max(0, $deadline->getTimestamp() - now()->getTimestamp());
    }

    public function adminAnalytics(): array
    {
        $courseIds = \App\Models\Course::pluck('id');

        $lessonIds = Lesson::whereIn('course_id', $courseIds)->pluck('id');

        $avgScore = (float) QuizAttempt::whereIn('lesson_id', $lessonIds)
            ->whereNotNull('submitted_at')
            ->avg('score');

        $passRate = QuizAttempt::whereIn('lesson_id', $lessonIds)
            ->whereNotNull('submitted_at')
            ->selectRaw('AVG(CASE WHEN passed = 1 THEN 100 ELSE 0 END) as rate')
            ->value('rate');

        $strugglingLessons = Lesson::whereIn('id', $lessonIds)
            ->with('course')
            ->withAvg(['quizAttempts as avg_score' => fn ($q) => $q->whereNotNull('submitted_at')], 'score')
            ->withCount(['quizAttempts as attempts_count' => fn ($q) => $q->whereNotNull('submitted_at')])
            ->get()
            ->filter(fn (Lesson $lesson) => $lesson->attempts_count > 0)
            ->sortBy('avg_score')
            ->take(5)
            ->values();

        $dropOff = Lesson::whereIn('course_id', $courseIds)
            ->with('course')
            ->withCount([
                'progress as started_count',
                'progress as completed_count' => fn ($q) => $q->where('completed', true),
            ])
            ->get()
            ->filter(fn (Lesson $lesson) => $lesson->started_count > 0)
            ->map(function (Lesson $lesson) {
                $drop = $lesson->started_count - $lesson->completed_count;
                $lesson->drop_off_rate = $lesson->started_count > 0
                    ? round(($drop / $lesson->started_count) * 100)
                    : 0;

                return $lesson;
            })
            ->sortByDesc('drop_off_rate')
            ->take(5)
            ->values();

        return [
            'avg_score' => round($avgScore),
            'pass_rate' => round((float) $passRate),
            'struggling_lessons' => $strugglingLessons,
            'drop_off_lessons' => $dropOff,
        ];
    }
}
