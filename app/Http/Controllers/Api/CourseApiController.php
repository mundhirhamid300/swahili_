<?php

/** Hii controller hupokea maombi ya mtumiaji na kuratibu jibu la sehemu hii ya mfumo. */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;

class CourseApiController extends Controller
{
    public function index()
    {
        $courses = Course::where('status', 'published')->withCount('lessons')->get();
        return response()->json($courses);
    }

    public function show(Course $course)
    {
        $course->load(['lessons' => fn ($q) => $q->where('status', 'published')]);
        return response()->json($course);
    }
}
