<?php

namespace App\Models;

use App\Models\Concerns\UsesLearningRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Huwakilisha kadi ya kujifunzia neno au kifungu cha Kiswahili. */
class Flashcard extends Model
{
    use UsesLearningRecords; // Tumia tabia zinazogawanywa na rekodi za kujifunza.

    protected $table = 'learning_records'; // Hifadhi flashcard katika jedwali la pamoja la rekodi za kujifunza.

    /** Safu salama za data ya flashcard. */
    protected $fillable = [
        'lesson_id', // Somo linalomiliki kadi hii.
        'swahili_word', // Neno au kifungu cha Kiswahili.
        'english_meaning', // Tafsiri au maana ya Kiingereza.
        'pronunciation', // Mwongozo wa matamshi.
        'audio_path', // Njia ya faili la sauti.
    ];

    /** Rudisha somo ambalo flashcard hii ni sehemu yake. */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
