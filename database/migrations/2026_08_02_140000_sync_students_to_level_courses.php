<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        foreach (DB::table('users')->where('role', 'student')->get() as $student) {
            $courseIds = DB::table('courses')
                ->where('level', $student->learning_level)
                ->where('status', 'published')
                ->pluck('id');

            DB::table('enrollments')
                ->where('user_id', $student->id)
                ->whereNotIn('course_id', $courseIds)
                ->delete();

            foreach ($courseIds as $courseId) {
                DB::table('enrollments')->insertOrIgnore([
                    'user_id' => $student->id,
                    'course_id' => $courseId,
                    'status' => 'active',
                    'enrolled_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Level enrollment replaces individual course enrollment.
    }
};
