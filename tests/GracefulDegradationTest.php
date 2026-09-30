<?php

namespace Klytron\LaravelScheduleTelegramOutput\Tests;

use Illuminate\Console\Scheduling\Event;

class GracefulDegradationTest extends TestCase
{
    /** @test */
    public function it_noops_gracefully_when_credentials_are_missing()
    {
        config()->set('schedule-telegram-output.bots.default.token', null);
        config()->set('schedule-telegram-output.default_chat_id', null);
        config()->set('schedule-telegram-output.strict_mode', false);

        $event = $this->app->make(Event::class, ['command' => 'inspire']);
        $result = $event->sendOutputToTelegram();

        $this->assertSame($event, $result);
    }

    /** @test */
    public function it_throws_in_strict_mode_when_credentials_are_missing()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Telegram credentials missing');

        config()->set('schedule-telegram-output.bots.default.token', null);
        config()->set('schedule-telegram-output.default_chat_id', null);
        config()->set('schedule-telegram-output.strict_mode', true);

        $event = $this->app->make(Event::class, ['command' => 'inspire']);
        $event->sendOutputToTelegram();
    }
}
