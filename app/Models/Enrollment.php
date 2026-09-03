<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Huhifadhi usajili wa mwanafunzi kwenye kozi. */
class Enrollment extends Model
{
    /** Safu zinazoruhusiwa kupokea data ya usajili. */
    protected $fillable = [
        'user_id', // Kitambulisho cha mwanafunzi.
        'course_id', // Kitambulisho cha kozi aliyosajiliwa.
        'status', // Hali ya usajili.
        'enrolled_at', // Tarehe na muda wa kusajiliwa.
    ];

    /** Geuza muda wa usajili kuwa kitu cha tarehe cha Laravel. */
    protected function casts(): array
    {
        return [
            'enrolled_at' => 'datetime',
        ];
    }

    /** Rudisha mwanafunzi anayehusishwa na usajili huu. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Rudisha kozi inayohusishwa na usajili huu. */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
