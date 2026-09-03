<?php

namespace Tests\Unit;

use App\Exceptions\AiServiceException;
use App\Models\User;
use App\Services\Ai\OpenAiLanguageService;
use App\Services\AiQuotaService;
use App\Services\ChatbotService;
use Mockery;
use Tests\TestCase;

class ChatbotServiceTest extends TestCase
{
    public function test_it_uses_the_local_dictionary_when_openai_is_unreachable(): void
    {
        $language = Mockery::mock(OpenAiLanguageService::class);
        $language->shouldReceive('swahiliLearningReply')
            ->once()
            ->andThrow(new AiServiceException('Network unavailable', 'openai'));

        $quota = Mockery::mock(AiQuotaService::class);
        $quota->shouldReceive('assertWithinQuota')->once();
        $quota->shouldNotReceive('consume');

        $user = new User;
        $user->id = 123;

        $result = (new ChatbotService($language, $quota))->respond($user, 'Thank you!');

        $this->assertSame('Asante', $result['details']['swahili']);
        $this->assertSame('Thank you', $result['details']['meaning']);
        $this->assertStringContainsString('kamusi ya ndani', $result['details']['note']);
    }

    public function test_it_returns_a_friendly_offline_message_for_an_unknown_phrase(): void
    {
        $language = Mockery::mock(OpenAiLanguageService::class);
        $language->shouldReceive('swahiliLearningReply')
            ->once()
            ->andThrow(new AiServiceException('Network unavailable', 'openai'));

        $quota = Mockery::mock(AiQuotaService::class);
        $quota->shouldReceive('assertWithinQuota')->once();
        $quota->shouldNotReceive('consume');

        $result = (new ChatbotService($language, $quota))->respond(new User, 'An unknown long phrase');

        $this->assertStringContainsString('haipatikani kwa muda', $result['response']);
        $this->assertSame('', $result['details']['swahili']);
    }
}
