<?php

/** Hii controller hupokea maombi ya mtumiaji na kuratibu jibu la sehemu hii ya mfumo. */

namespace App\Http\Controllers\Api;

use App\Exceptions\AiServiceException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChatbotRequest;
use App\Services\ChatbotService;

class ChatbotApiController extends Controller
{
    public function __construct(private ChatbotService $chatbotService) {}

    public function send(ChatbotRequest $request)
    {
        try {
            $mode = $request->input('mode', 'tutor');
            $result = $this->chatbotService->respond(
                $request->user(),
                $request->message,
                $mode,
                $request->integer('lesson_id') ?: null,
                $request->input('conversation_id')
            );

            return response()->json([
                'success' => true,
                'response' => $result['response'],
                'conversation_id' => $result['conversation_id'],
                'mode' => $mode,
            ]);
        } catch (AiServiceException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->provider === 'quota'
                    ? 'You have reached your limit for today’s practice. Please continue again tomorrow.'
                    : 'Sorry, AI Teacher is currently unavailable. Please try again after a short while.',
            ], 502);
        }
    }
}
