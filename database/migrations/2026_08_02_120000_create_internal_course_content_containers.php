<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        foreach (DB::table('courses')->get() as $course) {
            if (! DB::table('lessons')->where('course_id', $course->id)->exists()) {
                DB::table('lessons')->insert([
                    'course_id' => $course->id,
                    'title' => $course->title,
                    'content' => $course->description,
                    'lesson_order' => 1,
                    'status' => $course->status,
                    'quiz_pass_mark' => 70,
                    'quiz_allow_retake' => 0,
                    'quiz_max_attempts' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Internal containers may hold user-created words, so rollback must not delete them.
    }
};
