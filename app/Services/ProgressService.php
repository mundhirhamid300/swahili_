<?php

/** Hii service ina mantiki ya biashara inayotumiwa na sehemu hii ya mfumo. */

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Progress;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class ProgressService
{
    public function getStudentStats(User $user): array
    {
        return Cache::remember("student.{$user->id}.stats", 60, function () use ($user) {
            $enrolledCourses = Enrollment::where('user_id', $user->id)->count();
            $completedLessons = Progress::where('user_id', $user->id)->where('completed', true)->count();
            $avgScore = (int) QuizAttempt::where('user_id', $user->id)
                ->whereNotNull('submitted_at')
                ->avg('score');
            $weekStart = Carbon::now()->startOfWeek();
            $lessonsThisWeek = Progress::where('user_id', $user->id)
                ->where('completed', true)
                ->where('updated_at', '>=', $weekStart)
                ->count();

            $weeklyGoal = $user->weekly_goal ?: 5;
            $goalProgress = min(100, (int) round(($lessonsThisWeek / max(1, $weeklyGoal)) * 100));

            return compact(
                'enrolledCourses',
                'completedLessons',
                'avgScore',
                'lessonsThisWeek',
                'weeklyGoal',
                'goalProgress'
            ) + [
                'learning_streak' => $user->learning_streak,
                'streak_reminders' => (bool) $user->streak_reminders,
                'goal_met' => $lessonsThisWeek >= $weeklyGoal,
                'streak_at_risk' => $user->learning_streak > 0 && (! $user->last_learning_at || ! Carbon::parse($user->last_learning_at)->isToday()),
            ];
        });
    }

    public function clearStudentCache(User $user): void
    {
        Cache::forget("student.{$user->id}.stats");
        Cache::forget("student.{$user->id}.continue");
    }

    public function getContinueLearning(User $user): ?array
    {
        return Cache::remember("student.{$user->id}.continue", 60, function () use ($user) {
            $enrollment = Enrollment::where('user_id', $user->id)
                ->where('status', 'active')
                ->with(['course.lessons' => fn ($q) => $q->where('status', 'published')->orderBy('lesson_order')])
                ->latest()
                ->first();

            if (! $enrollment || $enrollment->course->lessons->isEmpty()) {
                return null;
            }

            $lessonIds = $enrollment->course->lessons->pluck('id');
            $completedIds = Progress::where('user_id', $user->id)
                ->whereIn('lesson_id', $lessonIds)
                ->where('completed', true)
                ->pluck('lesson_id');

            $nextLesson = $enrollment->course->lessons
                ->first(fn (Lesson $lesson) => ! $completedIds->contains($lesson->id))
                ?? $enrollment->course->lessons->first();

            $progress = $this->getCourseProgress($user, $enrollment->course);

            return [
                'course' => $enrollment->course,
                'lesson' => $nextLesson,
                'progress' => $progress,
            ];
        });
    }

    public function recordLearningActivity(User $user): void
    {
        $today = Carbon::today();
        $last = $user->last_learning_at ? Carbon::parse($user->last_learning_at) : null;

        if ($last && $last->isToday()) {
            return;
        }

        $streak = 1;
        if ($last && $last->isYesterday()) {
            $streak = $user->learning_streak + 1;
        }

        $user->update([
            'learning_streak' => $streak,
            'last_learning_at' => $today,
        ]);

        $this->clearStudentCache($user);
    }

    public function markLessonComplete(User $user, Lesson $lesson, int $score = 100): Progress
    {
        $this->recordLearningActivity($user);

        $progress = Progress::updateOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lesson->id],
            [
                'completed' => true,
                'score' => $score,
                'status' => 'completed',
            ]
        );

        $lesson->loadMissing('course');
        $this->checkCourseCompletion($user, $lesson->course);

        $this->clearStudentCache($user);

        return $progress;
    }

    public function markLessonInProgress(User $user, Lesson $lesson, int $score = 0): Progress
    {
        return Progress::updateOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lesson->id],
            [
                'completed' => false,
                'score' => $score,
                'status' => 'in_progress',
            ]
        );
    }

    public function updateLessonProgress(User $user, Lesson $lesson): void
    {
        $quizzes = Quiz::where('lesson_id', $lesson->id)->pluck('id');
        $totalQuizzes = $quizzes->count();

        if ($totalQuizzes === 0) {
            $this->markLessonComplete($user, $lesson);

            return;
        }

        $latestAttempt = QuizAttempt::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->whereNotNull('submitted_at')
            ->latest('id')
            ->first();

        if ($latestAttempt && $latestAttempt->passed) {
            $this->markLessonComplete($user, $lesson, $latestAttempt->score);

            return;
        }

        $openAttempt = QuizAttempt::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->whereNull('submitted_at')
            ->latest('id')
            ->first();

        if ($openAttempt) {
            $answered = QuizAnswer::where('quiz_attempt_id', $openAttempt->id)->count();
            if ($answered > 0) {
                $this->markLessonInProgress($user, $lesson);
            }
        }
    }

    public function getCourseProgress(User $user, Course $course): array
    {
        $lessonIds = $course->lessons()->where('status', 'published')->pluck('id');
        $total = $lessonIds->count();
        $completed = Progress::where('user_id', $user->id)
            ->whereIn('lesson_id', $lessonIds)
            ->where('completed', true)
            ->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'percentage' => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
        ];
    }

    /**
     * Per-lesson checklist toward a course certificate.
     *
     * @return array{progress: array, lessons: \Illuminate\Support\Collection, next_lesson: ?Lesson}
     */
    public function getCourseLearningPath(User $user, Course $course): array
    {
        $lessons = $course->lessons()
            ->where('status', 'published')
            ->orderBy('lesson_order')
            ->withCount('quizzes')
            ->get();

        $completedIds = Progress::where('user_id', $user->id)
            ->whereIn('lesson_id', $lessons->pluck('id'))
            ->where('completed', true)
            ->pluck('lesson_id');

        $path = $lessons->map(function (Lesson $lesson) use ($completedIds) {
            $done = $completedIds->contains($lesson->id);

            return [
                'lesson' => $lesson,
                'completed' => $done,
                'has_quiz' => $lesson->quizzes_count > 0,
                'action' => $done
                    ? 'done'
                    : ($lesson->quizzes_count > 0 ? 'pass_quiz' : 'open_lesson'),
            ];
        });

        $next = $path->firstWhere('completed', false);

        return [
            'progress' => $this->getCourseProgress($user, $course),
            'lessons' => $path,
            'next_lesson' => $next['lesson'] ?? null,
        ];
    }

    private function checkCourseCompletion(User $user, Course $course): void
    {
        if (! $course) {
            return;
        }

        $progress = $this->getCourseProgress($user, $course);

        if ($progress['percentage'] !== 100) {
            return;
        }

        Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->update(['status' => 'completed']);

        $this->clearStudentCache($user);
    }
}
