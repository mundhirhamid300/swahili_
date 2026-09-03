<?php

/** Hii controller hupokea maombi ya mtumiaji na kuratibu jibu la sehemu hii ya mfumo. */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lesson;

class LessonApiController extends Controller
{
    public function show(Lesson $lesson)
    {
        $lesson->load(['flashcards', 'quizzes', 'course']);
        return response()->json($lesson);
    }
}
