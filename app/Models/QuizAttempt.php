<?php

/** Hii model huwakilisha na kusimamia data ya sehemu hii ya mfumo. */

namespace App\Models;

use App\Models\Concerns\UsesLearningRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizAttempt extends Model
{
    use UsesLearningRecords;

    protected $table = 'learning_records';

    protected $fillable = [
        'user_id',
        'lesson_id',
        'current_quiz_id',
        'attempt_number',
        'started_at',
        'question_started_at',
        'submitted_at',
        'score',
        'passed',
        'time_spent_seconds',
        'admin_feedback',
        'admin_feedback_at',
        'admin_feedback_by',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'question_started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'passed' => 'boolean',
            'admin_feedback_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function feedbackAuthor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_feedback_by');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class);
    }

    public function isOpen(): bool
    {
        return $this->submitted_at === null;
    }
}
