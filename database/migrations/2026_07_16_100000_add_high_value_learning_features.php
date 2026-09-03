<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pronunciation_attempts', function (Blueprint $table) {
            $table->string('transcript')->nullable()->after('phrase');
            $table->string('feedback', 500)->nullable()->after('duration_ms');
            $table->boolean('used_stt')->default(false)->after('feedback');
        });

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->text('teacher_feedback')->nullable()->after('time_spent_seconds');
            $table->timestamp('teacher_feedback_at')->nullable()->after('teacher_feedback');
            $table->foreignId('teacher_feedback_by')->nullable()->after('teacher_feedback_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('teacher_feedback_by');
            $table->dropColumn(['teacher_feedback', 'teacher_feedback_at']);
        });

        Schema::table('pronunciation_attempts', function (Blueprint $table) {
            $table->dropColumn(['transcript', 'feedback', 'used_stt']);
        });
    }
};
