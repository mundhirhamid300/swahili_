<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The new learning system starts with clean content while preserving all user accounts.
        DB::table('courses')->delete();
        DB::table('users')->where('learning_level', 'advanced')->update(['learning_level' => 'intermediate']);

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE courses MODIFY COLUMN level ENUM('beginner', 'intermediate') NOT NULL DEFAULT 'beginner'");
            DB::statement("ALTER TABLE users MODIFY COLUMN learning_level ENUM('beginner', 'intermediate') NOT NULL DEFAULT 'beginner'");
        }

        $now = now();
        DB::table('courses')->insert([
            [
                'title' => 'Beginner Kiswahili',
                'description' => 'Start with essential Swahili words, greetings and everyday expressions.',
                'level' => 'beginner',
                'topic' => 'Foundations',
                'status' => 'published',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Intermediate Kiswahili',
                'description' => 'Build longer sentences and improve everyday Swahili conversation.',
                'level' => 'intermediate',
                'topic' => 'Conversation',
                'status' => 'published',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE courses MODIFY COLUMN level ENUM('beginner', 'intermediate', 'advanced') NOT NULL DEFAULT 'beginner'");
            DB::statement("ALTER TABLE users MODIFY COLUMN learning_level ENUM('beginner', 'intermediate', 'advanced') NOT NULL DEFAULT 'beginner'");
        }
    }
};
