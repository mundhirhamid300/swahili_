<?php

namespace App\Models;

use App\Models\Concerns\UsesLearningRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Huhifadhi maendeleo ya mwanafunzi kwa somo. */
class Progress extends Model
{
    use UsesLearningRecords; // Tumia tabia za rekodi za kujifunza.

    protected $table = 'learning_records'; // Tumia jedwali la pamoja la rekodi.

    /** Safu zinazoweza kuhifadhiwa kwa rekodi ya maendeleo. */
    protected $fillable = [
        'user_id', // Mwanafunzi anayepimwa.
        'lesson_id', // Somo linalofuatiliwa.
        'completed', // Huonyesha kama somo limekamilika.
        'score', // Alama iliyopatikana.
        'status', // Hali ya maendeleo.
    ];

    /** Geuza thamani ya ukamilishaji kuwa boolean. */
    protected function casts(): array
    {
        return [
            'completed' => 'boolean',
        ];
    }

    /** Rudisha mwanafunzi wa rekodi hii ya maendeleo. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Rudisha somo linalohusiana na maendeleo haya. */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
