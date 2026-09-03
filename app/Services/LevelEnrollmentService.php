<?php

/** Hii service ina mantiki ya biashara inayotumiwa na sehemu hii ya mfumo. */

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LevelEnrollmentService
{
    public function enroll(User $user, string $level): void
    {
        DB::transaction(function () use ($user, $level) {
            $user->update(['learning_level' => $level]);
            Enrollment::where('user_id', $user->id)->delete();

            Course::where('level', $level)->where('status', 'published')->each(function (Course $course) use ($user) {
                Enrollment::create([
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                    'status' => 'active',
                    'enrolled_at' => now(),
                ]);
            });
        });
    }

    public function attachPublishedCourse(Course $course): void
    {
        if ($course->status !== 'published') return;

        User::where('role', 'student')->where('learning_level', $course->level)->each(function (User $user) use ($course) {
            Enrollment::firstOrCreate(
                ['user_id' => $user->id, 'course_id' => $course->id],
                ['status' => 'active', 'enrolled_at' => now()]
            );
        });
    }
}
