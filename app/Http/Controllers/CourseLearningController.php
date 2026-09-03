<?php

/** Hii controller hupokea maombi ya mtumiaji na kuratibu jibu la sehemu hii ya mfumo. */

namespace App\Http\Controllers;

use App\Models\Course;
use App\Services\ProgressService;
use App\Support\EnrollmentGuard;

class CourseLearningController extends Controller
{
    public function __construct(private ProgressService $progressService) {}

    public function show(Course $course)
    {
        EnrollmentGuard::ensureEnrolled(auth()->user(), $course);
        abort_if($course->status !== 'published', 404);

        $lesson = $course->lessons()->with(['flashcards' => fn ($query) => $query->orderBy('id')])->first();
        if ($lesson) {
            $this->progressService->updateLessonProgress(auth()->user(), $lesson);
        }

        return view('courses.learn', compact('course', 'lesson'));
    }
}
