<?php

/** Hii model huwakilisha na kusimamia data ya sehemu hii ya mfumo. */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class XpEvent extends Model
{
    protected $fillable = [
        'user_id',
        'source',
        'points',
        'meta',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
