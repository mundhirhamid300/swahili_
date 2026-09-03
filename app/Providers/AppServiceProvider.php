<?php

/** Hii provider husajili mipangilio na huduma za programu wakati wa kuanza. */

namespace App\Providers;

use App\Models\Course;
use App\Models\Flashcard;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        Password::defaults(function () {
            return Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers();
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'|'.$request->ip());
        });

        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        RateLimiter::for('password-reset', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        Route::bind('lesson', function (string $value, $route) {
            $course = $route->parameter('course');

            if ($course instanceof Course) {
                return Lesson::where('course_id', $course->id)->whereKey($value)->firstOrFail();
            }

            return Lesson::whereKey($value)->firstOrFail();
        });

        Route::bind('flashcard', function (string $value, $route) {
            $lesson = $route->parameter('lesson');

            return Flashcard::where('lesson_id', $lesson->id)->whereKey($value)->firstOrFail();
        });

        Route::bind('quiz', function (string $value, $route) {
            $lesson = $route->parameter('lesson');

            return Quiz::where('lesson_id', $lesson->id)->whereKey($value)->firstOrFail();
        });

        Route::bind('attempt', function (string $value, $route) {
            $lesson = $route->parameter('lesson');

            return \App\Models\QuizAttempt::where('lesson_id', $lesson->id)->whereKey($value)->firstOrFail();
        });

        Route::bind('student', function (string $value) {
            return User::whereKey($value)->where('role', 'student')->firstOrFail();
        });
    }
}
