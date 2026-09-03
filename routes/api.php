<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\ChatbotApiController;
use App\Http\Controllers\Api\CourseApiController;
use App\Http\Controllers\Api\EnrollmentApiController;
use App\Http\Controllers\Api\LessonApiController;
use App\Http\Controllers\Api\ProgressApiController;
use App\Http\Controllers\Api\QuizApiController;
use App\Http\Controllers\Api\TranslationApiController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthApiController::class, 'login'])->middleware('throttle:api-login');
Route::post('/register', [AuthApiController::class, 'register'])->middleware('throttle:api-login');

Route::get('/translate/phrases', [TranslationApiController::class, 'phrases']);

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::get('/me', [AuthApiController::class, 'me']);

    Route::post('/translate', [TranslationApiController::class, 'translate'])->middleware('throttle:ai');
    Route::post('/translate/analyze', [TranslationApiController::class, 'analyze'])->middleware('throttle:ai');
    Route::post('/translate/speak', [TranslationApiController::class, 'speak'])->middleware('throttle:ai-tts');

    Route::get('/courses', [CourseApiController::class, 'index']);
    Route::get('/courses/{course}', [CourseApiController::class, 'show']);
    Route::post('/courses/{course}/enroll', [EnrollmentApiController::class, 'enroll']);
    Route::get('/my-courses', [EnrollmentApiController::class, 'myCourses']);

    Route::get('/lessons/{lesson}', [LessonApiController::class, 'show']);
    Route::post('/quizzes/{quiz}/submit', [QuizApiController::class, 'submit']);

    Route::get('/progress/stats', [ProgressApiController::class, 'stats']);
    Route::get('/progress/courses/{course}', [ProgressApiController::class, 'courseProgress']);

    Route::post('/chatbot', [ChatbotApiController::class, 'send'])->middleware('throttle:ai');
});
