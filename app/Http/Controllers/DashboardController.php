<?php

/** Hii controller hupokea maombi ya mtumiaji na kuratibu jibu la sehemu hii ya mfumo. */

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Services\DailyPlanService;
use App\Services\ProgressService;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function admin()
    {
        $stats = Cache::remember('admin.dashboard.stats', 90, function () {
            return [
                'users' => User::count(),
                'students' => User::where('role', 'student')->count(),
                'courses' => Course::count(),
                'enrollments' => Enrollment::count(),
            ];
        });

        return view('admin.dashboard', compact('stats'));
    }

    public function student(ProgressService $progressService, DailyPlanService $dailyPlanService)
    {
        $user = auth()->user();
        $stats = $progressService->getStudentStats($user);
        $continueLearning = $progressService->getContinueLearning($user);
        $todaysPlan = $dailyPlanService->forUser($user);
        $enrollments = Enrollment::where('user_id', $user->id)
            ->with('course:id,title,level,topic,status')
            ->latest()
            ->take(5)
            ->get();

        return view('student.dashboard', compact('stats', 'enrollments', 'continueLearning', 'todaysPlan'));
    }
}
