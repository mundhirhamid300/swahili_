<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Huwakilisha kozi moja inayosomwa kwenye mfumo. */
class Course extends Model
{
    /** Ruhusu taarifa hizi zijazwe kwa wingi wakati wa kuunda au kusasisha kozi. */
    protected $fillable = [
        'title', // Jina linaloonekana la kozi.
        'description', // Maelezo ya maudhui ya kozi.
        'level', // Kiwango cha ugumu, kwa mfano beginner.
        'topic', // Mada kuu inayofundishwa na kozi.
        'status', // Hali ya kozi, kama draft au published.
        'thumbnail', // Njia ya picha ya utambulisho wa kozi.
    ];

    /** Rudisha masomo ya kozi yakiwa yamepangwa kwa mpangilio wake wa kufundishwa. */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('lesson_order');
    }

    /** Rudisha miunganisho yote ya wanafunzi waliosajiliwa kwenye kozi hii. */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /** Thibitisha kama kozi inaweza kuonekana na wanafunzi. */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }
}
