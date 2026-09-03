<?php

/** Hii controller hupokea maombi ya mtumiaji na kuratibu jibu la sehemu hii ya mfumo. */

namespace App\Http\Controllers\Api;

use App\Exceptions\AiServiceException;
use App\Http\Controllers\Controller;
use App\Http\Requests\AiAnalyzeRequest;
use App\Http\Requests\SpeakRequest;
use App\Http\Requests\TranslateRequest;
use App\Services\SpeechService;
use App\Services\TranslationService;

class TranslationApiController extends Controller
{
    public function __construct(
        private TranslationService $translationService,
        private SpeechService $speechService
    ) {}

    public function translate(TranslateRequest $request)
    {
        try {
            $result = $this->translationService->translate(
                $request->text,
                $request->from,
                $request->to,
                $request->user()
            );

            return response()->json([
                'success' => true,
                'original' => $request->text,
                'translation' => $result['translation'],
                'pronunciation' => $result['pronunciation'],
                'source' => $result['source'],
            ]);
        } catch (AiServiceException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->toUserMessage(),
            ], 502);
        }
    }

    public function analyze(AiAnalyzeRequest $request)
    {
        try {
            $result = $this->translationService->analyze(
                $request->mode,
                $request->text,
                $request->input('from', 'en'),
                $request->input('to', 'sw'),
                $request->user()
            );

            return response()->json([
                'success' => true,
                'mode' => $result['mode'],
                'content' => $result['content'],
            ]);
        } catch (AiServiceException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->toUserMessage(),
            ], 502);
        }
    }

    public function speak(SpeakRequest $request)
    {
        $result = $this->speechService->synthesize(
            $request->text,
            $request->input('lang', 'sw')
        );

        return response()->json([
            'success' => true,
            'url' => $result['url'],
            'provider' => $result['provider'],
            'message' => $result['message'],
        ]);
    }

    public function phrases()
    {
        return response()->json([
            'phrases' => $this->translationService->getQuickPhrases(),
            'dictionary' => $this->translationService->getDictionary(),
        ]);
    }
}
