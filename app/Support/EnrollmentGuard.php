<?php

/** Hii helper ina kazi maalumu ya kusaidia sehemu nyingine za programu. */

namespace App\Support;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;

class EnrollmentGuard
{
    public static function ensureEnrolled(User $user, Course $course): void
    {
        if (! $user->isStudent()) {
            return;
        }

        $enrolled = $user->learning_level === $course->level;

        if (! $enrolled) {
            abort(403, 'Join the '.ucfirst($course->level).' level before accessing this course.');
        }
    }
}
