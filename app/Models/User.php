<?php

/** Hii model huwakilisha na kusimamia data ya sehemu hii ya mfumo. */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'role',
        'status',
        'country',
        'learning_level',
        'learning_streak',
        'last_learning_at',
        'weekly_goal',
        'xp',
        'leaderboard_opt_in',
        'streak_reminders',
        'avatar',
        'invitation_token_hash',
        'invitation_expires_at',
        'invitation_accepted_at',
        'must_change_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'invitation_token_hash',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_learning_at' => 'date',
            'leaderboard_opt_in' => 'boolean',
            'streak_reminders' => 'boolean',
            'invitation_expires_at' => 'datetime',
            'invitation_accepted_at' => 'datetime',
            'must_change_password' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin'], true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'super_admin' => 'Super Admin',
            'admin' => 'Administrator',
            default => 'Student',
        };
    }

    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar
            ? Storage::disk('public')->url($this->avatar)
            : asset('images/default-avatar.svg');
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(Progress::class);
    }

    public function quizAnswers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class);
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }
}
