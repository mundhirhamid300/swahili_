<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $renamingFeedbackAuthor = Schema::hasColumn('learning_records', 'teacher_feedback_by');

        if ($renamingFeedbackAuthor) {
            Schema::table('learning_records', function (Blueprint $table) {
                $table->dropForeign(['teacher_feedback_by']);
            });
        }

        Schema::table('learning_records', function (Blueprint $table) {
            if (Schema::hasColumn('learning_records', 'teacher_feedback')) {
                $table->renameColumn('teacher_feedback', 'admin_feedback');
            }
            if (Schema::hasColumn('learning_records', 'teacher_feedback_at')) {
                $table->renameColumn('teacher_feedback_at', 'admin_feedback_at');
            }
            if (Schema::hasColumn('learning_records', 'teacher_feedback_by')) {
                $table->renameColumn('teacher_feedback_by', 'admin_feedback_by');
            }
        });

        if ($renamingFeedbackAuthor) {
            Schema::table('learning_records', function (Blueprint $table) {
                $table->foreign('admin_feedback_by')->references('id')->on('users')->nullOnDelete();
            });
        }

        Schema::table('courses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('teacher_id');
        });

        DB::table('users')->where('role', 'teacher')->delete();

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'student') NOT NULL DEFAULT 'student'");
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'teacher', 'student') NOT NULL DEFAULT 'student'");
        }

        Schema::table('courses', function (Blueprint $table) {
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::table('learning_records', function (Blueprint $table) {
            $table->dropForeign(['admin_feedback_by']);
            $table->renameColumn('admin_feedback', 'teacher_feedback');
            $table->renameColumn('admin_feedback_at', 'teacher_feedback_at');
            $table->renameColumn('admin_feedback_by', 'teacher_feedback_by');
            $table->foreign('teacher_feedback_by')->references('id')->on('users')->nullOnDelete();
        });
    }
};
