<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Huhifadhi ujumbe na majibu ya mazungumzo ya chatbot. */
class ChatbotSession extends Model
{
    /** Safu zinazojazwa wakati wa kurekodi ujumbe wa chatbot. */
    protected $fillable = [
        'user_id', // Mtumiaji aliyeanzisha mazungumzo.
        'conversation_id', // Kitambulisho kinachounganisha ujumbe wa mazungumzo moja.
        'message', // Ujumbe aliotuma mtumiaji.
        'response', // Jibu lililotolewa na chatbot.
        'mode', // Aina ya msaada au hali ya chatbot iliyotumika.
    ];

    /** Rudisha mtumiaji anayemiliki rekodi ya mazungumzo. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
