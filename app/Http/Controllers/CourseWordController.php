<?php

/** Hii controller hupokea maombi ya mtumiaji na kuratibu jibu la sehemu hii ya mfumo. */

namespace App\Http\Controllers;

use App\Http\Requests\StoreFlashcardRequest;
use App\Models\Course;
use App\Models\Flashcard;
use App\Models\Lesson;
use Illuminate\Support\Facades\Storage;

class CourseWordController extends Controller
{
    public function index(Course $course)
    {
        $lesson = $this->contentLesson($course);
        $words = $lesson->flashcards()->latest('id')->paginate(20);

        return view('course-words.index', compact('course', 'lesson', 'words'));
    }

    public function store(StoreFlashcardRequest $request, Course $course)
    {
        $lesson = $this->contentLesson($course);
        $data = $request->validated();
        $data['audio_path'] = $request->file('audio')->store('course-words/audio', 'public');
        unset($data['audio']);
        $lesson->flashcards()->create($data);

        return back()->with('success', 'Word, meaning and your recorded voice were saved.');
    }

    public function edit(Course $course, Flashcard $word)
    {
        $this->ensureWordBelongsToCourse($course, $word);
        $flashcard = $word;

        return view('course-words.edit', compact('course', 'flashcard'));
    }

    public function update(StoreFlashcardRequest $request, Course $course, Flashcard $word)
    {
        $this->ensureWordBelongsToCourse($course, $word);
        $data = $request->validated();

        if ($request->hasFile('audio')) {
            if ($word->audio_path) Storage::disk('public')->delete($word->audio_path);
            $data['audio_path'] = $request->file('audio')->store('course-words/audio', 'public');
        }

        unset($data['audio']);
        $word->update($data);

        return redirect()->route('courses.words.index', $course)->with('success', 'Word updated successfully.');
    }

    public function destroy(Course $course, Flashcard $word)
    {
        $this->ensureWordBelongsToCourse($course, $word);
        if ($word->audio_path) Storage::disk('public')->delete($word->audio_path);
        $word->delete();

        return back()->with('success', 'Word and its audio were deleted.');
    }

    private function contentLesson(Course $course): Lesson
    {
        return $course->lessons()->firstOrCreate(
            ['lesson_order' => 1],
            ['title' => $course->title, 'content' => $course->description, 'status' => $course->status]
        );
    }

    private function ensureWordBelongsToCourse(Course $course, Flashcard $word): void
    {
        abort_unless($word->lesson?->course_id === $course->id, 404);
    }
}
