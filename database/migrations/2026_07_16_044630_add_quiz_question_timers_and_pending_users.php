<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->unsignedSmallInteger('time_limit_seconds')->default(45)->after('correct_answer');
            $table->unsignedSmallInteger('sort_order')->default(0)->after('time_limit_seconds');
        });

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->foreignId('current_quiz_id')->nullable()->after('lesson_id')->constrained('quizzes')->nullOnDelete();
            $table->timestamp('question_started_at')->nullable()->after('started_at');
        });

        Schema::table('quiz_answers', function (Blueprint $table) {
            $table->boolean('timed_out')->default(false)->after('score');
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('pending', 'active', 'inactive') NOT NULL DEFAULT 'active'");
        } else {
            // SQLite / others: recreate check via temporary column if needed — string storage accepts pending.
        }
    }

    public function down(): void
    {
        Schema::table('quiz_answers', function (Blueprint $table) {
            $table->dropColumn('timed_out');
        });

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('current_quiz_id');
            $table->dropColumn('question_started_at');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn(['time_limit_seconds', 'sort_order']);
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('active', 'inactive') NOT NULL DEFAULT 'active'");
        }
    }
};
