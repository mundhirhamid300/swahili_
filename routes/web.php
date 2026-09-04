<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\AdminInvitationController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseLearningController;
use App\Http\Controllers\CourseWordController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AdminProgressController;
use App\Http\Controllers\UserController;
use App\Models\Course;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route(match (auth()->user()->role) {
            'super_admin', 'admin' => 'admin.dashboard',
            default => 'student.dashboard',
        })
        : view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:register');
    Route::get('/forgot-password', [PasswordResetController::class, 'requestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendOtp'])->name('password.email')->middleware('throttle:3,5');
    Route::get('/forgot-password/otp', [PasswordResetController::class, 'otpForm'])->name('password.otp');
    Route::post('/forgot-password/otp', [PasswordResetController::class, 'verifyOtp'])->name('password.otp.verify')->middleware('throttle:10,1');
    Route::post('/forgot-password/otp/resend', [PasswordResetController::class, 'resendOtp'])->name('password.otp.resend')->middleware('throttle:3,5');
    Route::get('/reset-password', [PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update')->middleware('throttle:5,1');
    Route::get('/admin-invitations/{token}', [AdminInvitationController::class, 'accept'])
        ->name('admin-invitations.accept')
        ->middleware('throttle:10,1');
});

Route::middleware(['auth', 'password.changed'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/change-temporary-password', [PasswordResetController::class, 'forceForm'])->name('password.force.form');
    Route::put('/change-temporary-password', [PasswordResetController::class, 'forceUpdate'])->name('password.force.update');
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password/otp', [AuthController::class, 'sendPasswordChangeOtp'])->name('profile.password.otp.send')->middleware('throttle:3,5');
    Route::get('/profile/password/otp', [AuthController::class, 'showPasswordChangeOtp'])->name('profile.password.otp');
    Route::post('/profile/password/otp/verify', [AuthController::class, 'verifyPasswordChangeOtp'])->name('profile.password.otp.verify')->middleware('throttle:10,1');
    Route::post('/profile/password/otp/resend', [AuthController::class, 'resendPasswordChangeOtp'])->name('profile.password.otp.resend')->middleware('throttle:3,5');

    Route::middleware('role:admin,super_admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
        Route::resource('students', StudentController::class)->except(['show']);
        Route::get('/enrollments', [EnrollmentController::class, 'index'])->name('enrollments.index');
        Route::get('/progress', [AdminProgressController::class, 'index'])->name('progress.index');
        Route::get('/progress/{course}', [AdminProgressController::class, 'show'])->name('progress.show');
        Route::post('/attempts/{attempt}/feedback', [AdminProgressController::class, 'storeFeedback'])->name('attempts.feedback');
    });

    Route::middleware('role:super_admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::post('/students/{student}/promote-admin', [UserController::class, 'promoteToAdmin'])
            ->name('students.promote-admin');
        Route::post('/users/{user}/transfer-super-admin', [UserController::class, 'transferSuperAdmin'])
            ->name('users.transfer-super-admin');
    });

    Route::middleware('role:admin,super_admin')->group(function () {
        Route::resource('courses', CourseController::class);
        Route::get('/courses/{course}/words', [CourseWordController::class, 'index'])->name('courses.words.index');
        Route::post('/courses/{course}/words', [CourseWordController::class, 'store'])->name('courses.words.store');
        Route::get('/courses/{course}/words/{word}/edit', [CourseWordController::class, 'edit'])->name('courses.words.edit');
        Route::put('/courses/{course}/words/{word}', [CourseWordController::class, 'update'])->name('courses.words.update');
        Route::delete('/courses/{course}/words/{word}', [CourseWordController::class, 'destroy'])->name('courses.words.destroy');
        Route::get('/courses/{courseId}/lessons/{legacy?}', function (int $courseId) {
            $course = Course::find($courseId) ?? Course::firstOrFail();

            return redirect()->route('courses.words.index', $course);
        })->where('legacy', '.*')->name('courses.legacy-lessons');
    });

    Route::middleware('role:student')->prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'student'])->name('dashboard');
        Route::get('/courses', [EnrollmentController::class, 'myCourses'])->name('my-courses');
        Route::get('/courses/available', [EnrollmentController::class, 'available'])->name('available-courses');
        Route::post('/levels/{level}/enroll', [EnrollmentController::class, 'enrollLevel'])->name('levels.enroll');
        Route::get('/progress', [EnrollmentController::class, 'progress'])->name('progress');
        Route::get('/courses/{course}/learn', [CourseLearningController::class, 'show'])->name('courses.learn');
        Route::get('/ai-tutor', [ChatbotController::class, 'index'])->name('chatbot');
        Route::post('/ai-tutor/message', [ChatbotController::class, 'send'])->name('chatbot.send')->middleware('throttle:20,1');
        Route::post('/ai-tutor/listen', [ChatbotController::class, 'speak'])->name('chatbot.speak')->middleware('throttle:20,1');
        Route::post('/ai-tutor/new', [ChatbotController::class, 'newConversation'])->name('chatbot.new');
        Route::get('/courses/{courseId}/lessons/{legacy?}', function (int $courseId) {
            $course = Course::find($courseId) ?? Course::where('status', 'published')->firstOrFail();

            return redirect()->route('student.courses.learn', $course);
        })->where('legacy', '.*')->name('legacy-lessons');
    });

});
