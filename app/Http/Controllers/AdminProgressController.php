<?php

/** Hii controller hupokea maombi ya mtumiaji na kuratibu jibu la sehemu hii ya mfumo. */

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Progress;
use App\Models\QuizAttempt;
use App\Services\ProgressService;
use App\Services\QuizService;
use Illuminate\Http\Request;

class AdminProgressController extends Controller
{
    public function __construct(
        private ProgressService $progressService,
        private QuizService $quizService
    ) {}

    public function index()
    {
        $courses = Course::withCount('enrollments')->get();
        $analytics = $this->quizService->adminAnalytics();
        $lessonIds = Course::with('lessons:id,course_id')->get()
            ->flatMap(fn ($course) => $course->lessons->pluck('id'));

        $needsFeedback = QuizAttempt::query()
            ->whereIn('lesson_id', $lessonIds)
            ->whereNotNull('submitted_at')
            ->where('passed', false)
            ->whereNull('admin_feedback')
            ->with(['user:id,name', 'lesson:id,title,course_id'])
            ->latest('submitted_at')
            ->limit(12)
            ->get();

        return view('admin.progress.index', compact('courses', 'analytics', 'needsFeedback'));
    }

    public function show(Course $course)
    {
        $enrollments = Enrollment::where('course_id', $course->id)
            ->with('user')
            ->get()
            ->map(function ($enrollment) use ($course) {
                $enrollment->progress_data = $this->progressService->getCourseProgress($enrollment->user, $course);
                $enrollment->completed_lessons = Progress::where('user_id', $enrollment->user_id)
                    ->whereIn('lesson_id', $course->lessons()->pluck('id'))
                    ->where('completed', true)
                    ->count();

                return $enrollment;
            });

        $attempts = QuizAttempt::query()
            ->whereIn('lesson_id', $course->lessons()->pluck('id'))
            ->whereNotNull('submitted_at')
            ->with(['user:id,name,email', 'lesson:id,title', 'feedbackAuthor:id,name'])
            ->latest('submitted_at')
            ->limit(40)
            ->get();

        return view('admin.progress.show', compact('course', 'enrollments', 'attempts'));
    }

    public function storeFeedback(Request $request, QuizAttempt $attempt)
    {
        $attempt->load('lesson.course', 'user');
        abort_unless($attempt->lesson?->course, 404);

        $data = $request->validate([
            'admin_feedback' => ['required', 'string', 'max:500'],
        ]);

        $attempt->update([
            'admin_feedback' => trim($data['admin_feedback']),
            'admin_feedback_at' => now(),
            'admin_feedback_by' => auth()->id(),
        ]);

        return back()->with('success', 'Feedback saved for '.$attempt->user->name.'.');
    }
}
