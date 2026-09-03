<?php

/** Hii controller hupokea maombi ya mtumiaji na kuratibu jibu la sehemu hii ya mfumo. */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class EnrollmentApiController extends Controller
{
    public function enroll(Request $request, Course $course)
    {
        $enrollment = Enrollment::firstOrCreate(
            ['user_id' => $request->user()->id, 'course_id' => $course->id],
            ['status' => 'active', 'enrolled_at' => now()]
        );

        return response()->json($enrollment, 201);
    }

    public function myCourses(Request $request)
    {
        $enrollments = $request->user()->enrollments()->with('course.lessons')->get();
        return response()->json($enrollments);
    }
}
