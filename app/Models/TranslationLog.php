<?php

/** Hii model huwakilisha na kusimamia data ya sehemu hii ya mfumo. */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TranslationLog extends Model
{
    protected $table = 'translations_log';

    protected $fillable = [
        'user_id',
        'original_text',
        'translated_text',
        'from_lang',
        'to_lang',
        'source',
        'mode',
        'tokens_used',
        'is_favorite',
    ];

    protected function casts(): array
    {
        return [
            'is_favorite' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
