<?php

/** Hii model huwakilisha na kusimamia data ya sehemu hii ya mfumo. */

namespace App\Models;

use App\Models\Concerns\UsesLearningRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAnswer extends Model
{
    use UsesLearningRecords;

    protected $table = 'learning_records';

    protected $fillable = [
        'quiz_attempt_id',
        'quiz_id',
        'user_id',
        'selected_answer',
        'is_correct',
        'score',
        'timed_out',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'timed_out' => 'boolean',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(QuizAttempt::class, 'quiz_attempt_id');
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
