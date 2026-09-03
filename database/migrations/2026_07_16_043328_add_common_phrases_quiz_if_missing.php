<?php

use App\Models\Lesson;
use App\Models\Quiz;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $lesson = Lesson::query()
            ->where('title', 'Common Phrases')
            ->whereHas('course', fn ($q) => $q->where('title', 'Swahili for Beginners'))
            ->first();

        if (! $lesson) {
            return;
        }

        if (Quiz::where('lesson_id', $lesson->id)->exists()) {
            return;
        }

        Quiz::create([
            'lesson_id' => $lesson->id,
            'question' => 'What does "Tafadhali" mean?',
            'option_a' => 'Sorry',
            'option_b' => 'Please',
            'option_c' => 'Welcome',
            'option_d' => 'Goodbye',
            'correct_answer' => 'b',
        ]);
    }

    public function down(): void
    {
        $lesson = Lesson::query()
            ->where('title', 'Common Phrases')
            ->whereHas('course', fn ($q) => $q->where('title', 'Swahili for Beginners'))
            ->first();

        if (! $lesson) {
            return;
        }

        Quiz::where('lesson_id', $lesson->id)
            ->where('question', 'What does "Tafadhali" mean?')
            ->delete();
    }
};
