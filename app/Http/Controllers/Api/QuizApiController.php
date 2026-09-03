<?php

/** Hii controller hupokea maombi ya mtumiaji na kuratibu jibu la sehemu hii ya mfumo. */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitQuizRequest;
use App\Models\Quiz;
use App\Services\QuizService;
use App\Support\EnrollmentGuard;
use Illuminate\Validation\ValidationException;

class QuizApiController extends Controller
{
    public function __construct(private QuizService $quizService) {}

    public function submit(SubmitQuizRequest $request, Quiz $quiz)
    {
        $user = $request->user();
        $lesson = $quiz->lesson;
        $course = $lesson->course;

        EnrollmentGuard::ensureEnrolled($user, $course);

        try {
            $answer = $this->quizService->submitAnswer(
                $user,
                $lesson,
                $quiz,
                $request->selected_answer
            );
        } catch (ValidationException $e) {
            return response()->json([
                'message' => collect($e->errors())->flatten()->first(),
                'errors' => $e->errors(),
            ], 422);
        }

        return response()->json([
            'answer' => $answer->load('attempt'),
            'correct' => $answer->is_correct,
            'correct_answer' => $quiz->correct_answer,
            'attempt' => $answer->attempt,
        ]);
    }
}
