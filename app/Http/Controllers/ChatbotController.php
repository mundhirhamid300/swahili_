<?php

/** Hii controller hupokea maombi ya mtumiaji na kuratibu jibu la sehemu hii ya mfumo. */

namespace App\Http\Controllers;

use App\Exceptions\AiServiceException;
use App\Http\Requests\ChatbotRequest;
use App\Http\Requests\SpeakRequest;
use App\Services\AiQuotaService;
use App\Services\ChatbotService;
use App\Services\SpeechService;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function __construct(
        private ChatbotService $chatbotService,
        private SpeechService $speechService,
        private AiQuotaService $quotaService
    ) {}

    public function index()
    {
        return view('chatbot.index', [
            'messages' => $this->chatbotService->messages(),
            'speechConfig' => $this->speechService->getSpeechConfig(),
            'aiRemaining' => $this->quotaService->remaining(auth()->user()),
        ]);
    }

    public function newConversation()
    {
        $this->chatbotService->clear();

        return redirect()->route('student.chatbot')->with('success', 'A fresh practice chat is ready.');
    }

    public function send(ChatbotRequest $request)
    {
        try {
            $result = $this->chatbotService->respond(auth()->user(), $request->message);

            return response()->json([
                'success' => true,
                'response' => $result['response'],
                'details' => $result['details'],
                'speak_text' => $result['details']['swahili'] ?: $result['response'],
                'ai_remaining' => $this->quotaService->remaining(auth()->user()),
            ]);
        } catch (AiServiceException $exception) {
            $message = $exception->provider === 'quota'
                ? 'Umefikia kikomo cha mazoezi ya leo. Tafadhali endelea tena kesho.'
                : 'Samahani, Mwalimu AI hapatikani kwa sasa. Tafadhali jaribu tena baada ya muda mfupi.';

            return response()->json([
                'success' => false,
                'message' => $message,
            ], $exception->provider === 'quota' ? 429 : 502);
        }
    }

    public function speak(SpeakRequest $request)
    {
        $result = $this->speechService->synthesize($request->text, 'sw');

        return response()->json(['success' => true] + $result);
    }
}
