<?php

/** Hii controller hupokea maombi ya mtumiaji na kuratibu jibu la sehemu hii ya mfumo. */

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Services\ProgressService;
use App\Services\LevelEnrollmentService;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function __construct(
        private ProgressService $progressService,
        private LevelEnrollmentService $levelEnrollmentService
    ) {}

    public function index(Request $request)
    {
        $query = Enrollment::with(['user', 'course']);

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                    ->orWhereHas('course', fn ($c) => $c->where('title', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        $enrollments = $query->latest()->paginate(15)->withQueryString();

        return view('admin.enrollments.index', compact('enrollments'));
    }

    public function myCourses()
    {
        $user = auth()->user();
        $enrollments = $user->enrollments()->with(['course.lessons' => fn ($q) => $q->orderBy('lesson_order')])->get();

        foreach ($enrollments as $enrollment) {
            $enrollment->learning_path = $this->progressService->getCourseLearningPath($user, $enrollment->course);
        }

        return view('student.my-courses', compact('enrollments'));
    }

    public function available(Request $request)
    {
        $query = Course::where('status', 'published')
            ->withCount('lessons');

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('topic', 'like', "%{$search}%");
            });
        }

        if ($level = $request->string('level')->toString()) {
            $query->where('level', $level);
        }

        if ($topic = $request->string('topic')->trim()->toString()) {
            $query->where('topic', 'like', "%{$topic}%");
        }

        $courses = $query->orderBy('level')->orderBy('title')->get();
        $topics = Course::where('status', 'published')
            ->whereNotNull('topic')
            ->where('topic', '!=', '')
            ->distinct()
            ->orderBy('topic')
            ->pluck('topic');

        return view('student.available-courses', compact('courses', 'topics'));
    }

    public function enrollLevel(string $level)
    {
        abort_unless(in_array($level, ['beginner', 'intermediate'], true), 404);
        $this->levelEnrollmentService->enroll(auth()->user(), $level);

        return redirect()->route('student.my-courses')
            ->with('success', 'You joined the '.ucfirst($level).' level. All courses in this level are now available.');
    }

    public function progress()
    {
        $user = auth()->user();
        $enrollments = $user->enrollments()->with('course')->get()->map(function ($enrollment) use ($user) {
            $enrollment->learning_path = $this->progressService->getCourseLearningPath($user, $enrollment->course);
            $enrollment->progress_data = $enrollment->learning_path['progress'];

            return $enrollment;
        });

        return view('student.progress', compact('enrollments'));
    }
}
