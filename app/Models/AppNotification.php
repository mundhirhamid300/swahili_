<?php

/** Hii model huwakilisha na kusimamia data ya sehemu hii ya mfumo. */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppNotification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'link',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead(): void
    {
        if ($this->read_at === null) {
            $this->update(['read_at' => now()]);
            \Illuminate\Support\Facades\Cache::forget("user.{$this->user_id}.header_notifications");
        }
    }

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }
}
