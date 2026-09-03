<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Huwakilisha somo moja ndani ya kozi. */
class Lesson extends Model
{
    /** Orodha ya safu salama kujazwa wakati wa kuhifadhi somo. */
    protected $fillable = [
        'course_id', // Kitambulisho cha kozi inayomiliki somo.
        'title', // Kichwa cha somo.
        'content', // Maudhui ya maandishi ya somo.
        'audio_path', // Njia ya sauti inayosaidia somo.
        'image_path', // Njia ya picha ya somo.
        'lesson_order', // Nafasi ya somo katika mpangilio wa kozi.
        'status', // Hali ya uchapishaji wa somo.
        'quiz_pass_mark', // Alama ya chini ya kupita jaribio.
        'quiz_time_limit', // Muda wa jumla unaoruhusiwa kwa jaribio.
        'quiz_allow_retake', // Huamua kama mwanafunzi anaweza kurudia jaribio.
        'quiz_max_attempts', // Kikomo cha idadi ya majaribio.
    ];

    /** Geuza thamani za hifadhidata kwenye aina sahihi za PHP. */
    protected function casts(): array
    {
        return [
            'quiz_allow_retake' => 'boolean',
        ];
    }

    /** Rudisha kozi ambayo somo hili ni sehemu yake. */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /** Rudisha kadi za kujikumbusha zinazohusiana na somo hili. */
    public function flashcards(): HasMany
    {
        return $this->hasMany(Flashcard::class);
    }

    /** Rudisha maswali ya jaribio la somo hili. */
    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    /** Rudisha rekodi za maendeleo ya wanafunzi kwa somo hili. */
    public function progress(): HasMany
    {
        return $this->hasMany(Progress::class);
    }

    /** Rudisha majaribio yote ya quiz yaliyofanywa kwa somo hili. */
    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }
}
