<?php

/** Hii controller hupokea maombi ya mtumiaji na kuratibu jibu la sehemu hii ya mfumo. */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\ProgressService;
use Illuminate\Http\Request;

class ProgressApiController extends Controller
{
    public function __construct(private ProgressService $progressService) {}

    public function stats(Request $request)
    {
        return response()->json($this->progressService->getStudentStats($request->user()));
    }

    public function courseProgress(Request $request, Course $course)
    {
        return response()->json($this->progressService->getCourseProgress($request->user(), $course));
    }
}
