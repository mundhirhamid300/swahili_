<?php

/** Hii controller hupokea maombi ya mtumiaji na kuratibu jibu la sehemu hii ya mfumo. */

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Models\Course;
use App\Models\Enrollment;
use App\Services\LevelEnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    public function __construct(private LevelEnrollmentService $levelEnrollmentService) {}

    public function index(Request $request)
    {
        $query = Course::with(['lessons' => fn ($query) => $query->withCount('flashcards')])->withCount('enrollments');

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

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        $courses = $query->orderBy('level')->orderBy('title')->get();
        $topics = Course::whereNotNull('topic')->where('topic', '!=', '')->distinct()->orderBy('topic')->pluck('topic');

        return view('courses.index', compact('courses', 'topics'));
    }

    public function create()
    {
        return view('courses.create');
    }

    public function store(StoreCourseRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        $course = Course::create($data);
        $course->lessons()->create([
            'title' => $course->title,
            'content' => $course->description,
            'lesson_order' => 1,
            'status' => $course->status,
        ]);
        $this->levelEnrollmentService->attachPublishedCourse($course);

        return redirect()->route('courses.words.index', $course)
            ->with('success', 'Course created. Add its words, meanings and your voice below.');
    }

    public function show(Course $course)
    {
        $course->load(['lessons' => fn ($q) => $q->where('status', 'published')]);

        return view('courses.show', compact('course'));
    }

    public function edit(Course $course)
    {
        return view('courses.edit', compact('course'));
    }

    public function update(StoreCourseRequest $request, Course $course)
    {
        $oldLevel = $course->level;
        $oldStatus = $course->status;
        $data = $request->validated();

        if ($request->hasFile('thumbnail')) {
            if ($course->thumbnail) {
                Storage::disk('public')->delete($course->thumbnail);
            }
            $data['thumbnail'] = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        $course->update($data);
        if ($oldLevel !== $course->level || $oldStatus !== $course->status) {
            Enrollment::where('course_id', $course->id)->delete();
            $this->levelEnrollmentService->attachPublishedCourse($course);
        }
        $course->lessons()->firstOrCreate(
            ['lesson_order' => 1],
            ['title' => $course->title, 'content' => $course->description, 'status' => $course->status]
        )->update([
            'title' => $course->title,
            'content' => $course->description,
            'status' => $course->status,
        ]);
        return redirect()->route('courses.index')->with('success', 'Course updated successfully.');
    }

    public function destroy(Course $course)
    {
        $course->load('lessons.flashcards');
        foreach ($course->lessons as $lesson) {
            foreach ($lesson->flashcards as $word) {
                if ($word->audio_path) {
                    Storage::disk('public')->delete($word->audio_path);
                }
            }
        }

        if ($course->thumbnail) {
            Storage::disk('public')->delete($course->thumbnail);
        }
        $course->delete();

        return redirect()->route('courses.index')->with('success', 'Course deleted successfully.');
    }
}
