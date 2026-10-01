<?php

namespace Klytron\LaravelScheduleTelegramOutput\Tests;

use Illuminate\Console\Scheduling\CacheEventMutex;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Klytron\LaravelScheduleTelegramOutput\TelegramEvent;

class TelegramEventLegacyPathTest extends TestCase
{
    protected function makeEvent(string $command = 'php artisan inspire'): TelegramEvent
    {
        return new TelegramEvent(
            $this->app->make(CacheEventMutex::class),
            $command,
            'UTC'
        );
    }

    /** @test */
    public function it_noops_gracefully_when_credentials_are_missing()
    {
        config()->set('schedule-telegram-output.bots.default.token', null);
        config()->set('schedule-telegram-output.default_chat_id', null);
        config()->set('schedule-telegram-output.strict_mode', false);

        $event = $this->makeEvent();
        $result = $event->sendOutputToTelegram();

        $this->assertSame($event, $result);
    }

    /** @test */
    public function it_noops_gracefully_when_credentials_are_placeholders()
    {
        Http::fake();

        config()->set('schedule-telegram-output.bots.default.token', 'dummy');
        config()->set('schedule-telegram-output.default_chat_id', 'dummy');
        config()->set('schedule-telegram-output.strict_mode', false);

        $event = $this->makeEvent();
        $result = $event->sendOutputToTelegram('dummy');

        $this->assertSame($event, $result);
        Http::assertNothingSent();
    }

    /** @test */
    public function it_throws_in_strict_mode_when_credentials_are_missing()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Telegram credentials missing');

        config()->set('schedule-telegram-output.bots.default.token', null);
        config()->set('schedule-telegram-output.default_chat_id', null);
        config()->set('schedule-telegram-output.strict_mode', true);

        $this->makeEvent()->sendOutputToTelegram();
    }

    /** @test */
    public function it_kill_switch_wins_over_strict_mode()
    {
        config()->set('schedule-telegram-output.enabled', false);
        config()->set('schedule-telegram-output.bots.default.token', null);
        config()->set('schedule-telegram-output.default_chat_id', null);
        config()->set('schedule-telegram-output.strict_mode', true);

        $event = $this->makeEvent();
        $result = $event->sendOutputToTelegram();

        $this->assertSame($event, $result);
    }

    /** @test */
    public function it_passes_metadata_footer_through_the_legacy_path()
    {
        config()->set('schedule-telegram-output.bots.default.token', 'test-token');
        config()->set('schedule-telegram-output.message_format.parse_mode', 'MarkdownV2');

        Http::fake([
            'https://api.telegram.org/*' => Http::response(['ok' => true], 200),
        ]);

        $event = $this->makeEvent();
        $method = new \ReflectionMethod(TelegramEvent::class, 'sendTelegramMessage');
        $method->setAccessible(true);
        $method->invoke($event, '123456789', 'legacy output', [
            'signature' => 'php artisan inspire',
            'exit_code' => 1,
            'duration' => '42s',
            'env' => 'prod',
        ]);

        Http::assertSent(function (Request $request) {
            $json = $request->data();

            return ($json['chat_id'] ?? null) === '123456789'
                && str_contains((string) ($json['text'] ?? ''), 'exit 1')
                && str_contains((string) ($json['text'] ?? ''), '42s')
                && str_contains((string) ($json['text'] ?? ''), 'prod');
        });
    }

    /** @test */
    public function it_rethrows_in_fail_hard_mode()
    {
        $this->expectException(\RuntimeException::class);

        config()->set('schedule-telegram-output.bots.default.token', 'test-token');
        config()->set('schedule-telegram-output.fail_hard', true);
        config()->set('schedule-telegram-output.retry_attempts', 1);

        Http::fake([
            'https://api.telegram.org/*' => Http::response(['ok' => false], 500),
        ]);

        $event = $this->makeEvent();
        $method = new \ReflectionMethod(TelegramEvent::class, 'sendTelegramMessage');
        $method->setAccessible(true);
        $method->invoke($event, '123456789', 'legacy output', ['exit_code' => 1]);
    }
}
