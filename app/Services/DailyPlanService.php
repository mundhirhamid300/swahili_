<?php

/** Hii service ina mantiki ya biashara inayotumiwa na sehemu hii ya mfumo. */

namespace App\Services;

use App\Models\Flashcard;
use App\Models\User;

class DailyPlanService
{
    public function __construct(private ProgressService $progressService) {}

    /**
     * One screen plan: 1 lesson + up to 5 flashcards + short quiz pointer.
     *
     * @return array{
     *     course: \App\Models\Course,
     *     lesson: \App\Models\Lesson,
     *     flashcards: \Illuminate\Support\Collection<int, Flashcard>,
     *     has_quiz: bool,
     *     quiz_count: int,
     *     progress: array
     * }|null
     */
    public function forUser(User $user): ?array
    {
        $continue = $this->progressService->getContinueLearning($user);

        if (! $continue) {
            return null;
        }

        $course = $continue['course'];
        $lesson = $continue['lesson'];
        $lesson->loadMissing('quizzes');

        $flashcards = Flashcard::query()
            ->where('lesson_id', $lesson->id)
            ->orderBy('id')
            ->limit(5)
            ->get(['id', 'lesson_id', 'swahili_word', 'english_meaning', 'pronunciation']);

        if ($flashcards->count() < 5) {
            $needed = 5 - $flashcards->count();
            $extra = Flashcard::query()
                ->whereHas('lesson', fn ($q) => $q->where('course_id', $course->id)->where('status', 'published'))
                ->whereNotIn('id', $flashcards->pluck('id'))
                ->orderBy('id')
                ->limit($needed)
                ->get(['id', 'lesson_id', 'swahili_word', 'english_meaning', 'pronunciation']);
            $flashcards = $flashcards->concat($extra)->values();
        }

        $quizCount = $lesson->quizzes->count();

        return [
            'course' => $course,
            'lesson' => $lesson,
            'flashcards' => $flashcards,
            'has_quiz' => $quizCount > 0,
            'quiz_count' => min(5, max(1, $quizCount)),
            'progress' => $continue['progress'],
        ];
    }
}
