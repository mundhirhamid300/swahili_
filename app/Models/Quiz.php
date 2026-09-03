<?php

namespace App\Models;

use App\Models\Concerns\UsesLearningRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Huwakilisha swali moja la jaribio ndani ya somo. */
class Quiz extends Model
{
    use UsesLearningRecords; // Tumia tabia za rekodi za kujifunza zinazoshirikiwa.

    protected $table = 'learning_records'; // Maswali yanahifadhiwa kwenye jedwali hili la pamoja.

    /** Safu zinazoweza kujazwa kwa data ya swali. */
    protected $fillable = [
        'lesson_id', // Somo linalomiliki swali.
        'question', // Maandishi ya swali.
        'option_a', // Jibu la chaguo A.
        'option_b', // Jibu la chaguo B.
        'option_c', // Jibu la chaguo C.
        'option_d', // Jibu la chaguo D.
        'correct_answer', // Herufi ya chaguo sahihi.
        'time_limit_seconds', // Sekunde za kujibu swali hili.
        'sort_order', // Nafasi ya swali kwenye jaribio.
    ];

    /** Rudisha somo linalokuwa na swali hili. */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /** Rudisha majibu yote yaliyowahi kutolewa kwa swali hili. */
    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class);
    }

    /** Badilisha herufi ya chaguo kuwa maandishi yake kamili. */
    public function getOptionText(string $key): string
    {
        return match ($key) { // Linganisha herufi iliyotumwa na safu ya chaguo husika.
            'a' => $this->option_a, // Rudisha maandishi ya chaguo A.
            'b' => $this->option_b, // Rudisha maandishi ya chaguo B.
            'c' => $this->option_c, // Rudisha maandishi ya chaguo C.
            'd' => $this->option_d, // Rudisha maandishi ya chaguo D.
            default => '', // Rudisha tupu kama herufi haijatambulika.
        };
    }
}
