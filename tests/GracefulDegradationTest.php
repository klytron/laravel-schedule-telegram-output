<?php

namespace Klytron\LaravelScheduleTelegramOutput\Tests;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Support\Facades\Http;
use Klytron\LaravelScheduleTelegramOutput\TelegramNotifier;

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

    /** @test */
    public function it_treats_placeholder_credentials_as_missing()
    {
        foreach (['dummy', 'placeholder', 'example-token', 'your-telegram-bot-token', 'changeme', 'test'] as $placeholder) {
            $this->assertTrue(
                TelegramNotifier::isMissingOrPlaceholder($placeholder),
                "Expected '{$placeholder}' to be treated as missing"
            );
        }

        $this->assertFalse(TelegramNotifier::isMissingOrPlaceholder('1234567890'));
        $this->assertFalse(TelegramNotifier::isMissingOrPlaceholder('110201543:AAHdqTcvCH1vGWJxfSeofSAs0K5PALDsaw'));
    }

    /** @test */
    public function it_noops_when_token_is_a_placeholder()
    {
        Http::fake();

        config()->set('schedule-telegram-output.bots.default.token', 'dummy');
        config()->set('schedule-telegram-output.default_chat_id', '123456789');
        config()->set('schedule-telegram-output.strict_mode', false);

        $event = $this->app->make(Event::class, ['command' => 'inspire']);
        $result = $event->sendOutputToTelegram();

        $this->assertSame($event, $result);
        Http::assertNothingSent();
    }

    /** @test */
    public function it_noops_when_chat_id_is_a_placeholder()
    {
        Http::fake();

        config()->set('schedule-telegram-output.bots.default.token', 'real-token-value');
        config()->set('schedule-telegram-output.default_chat_id', 'placeholder');
        config()->set('schedule-telegram-output.strict_mode', false);

        $event = $this->app->make(Event::class, ['command' => 'inspire']);
        $result = $event->sendOutputToTelegram();

        $this->assertSame($event, $result);
        Http::assertNothingSent();
    }

    /** @test */
    public function it_throws_in_strict_mode_when_credentials_are_placeholders()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Telegram credentials missing');

        config()->set('schedule-telegram-output.bots.default.token', 'dummy');
        config()->set('schedule-telegram-output.default_chat_id', 'dummy');
        config()->set('schedule-telegram-output.strict_mode', true);

        $event = $this->app->make(Event::class, ['command' => 'inspire']);
        $event->sendOutputToTelegram();
    }

    /** @test */
    public function it_noops_when_disabled_even_with_valid_credentials()
    {
        Http::fake();

        config()->set('schedule-telegram-output.enabled', false);
        config()->set('schedule-telegram-output.bots.default.token', 'real-token-value');
        config()->set('schedule-telegram-output.default_chat_id', '123456789');
        config()->set('schedule-telegram-output.strict_mode', false);

        $event = $this->app->make(Event::class, ['command' => 'inspire']);
        $result = $event->sendOutputToTelegram();

        $this->assertSame($event, $result);
        Http::assertNothingSent();
    }

    /** @test */
    public function it_kill_switch_wins_over_strict_mode()
    {
        config()->set('schedule-telegram-output.enabled', false);
        config()->set('schedule-telegram-output.bots.default.token', null);
        config()->set('schedule-telegram-output.default_chat_id', null);
        config()->set('schedule-telegram-output.strict_mode', true);

        $event = $this->app->make(Event::class, ['command' => 'inspire']);
        $result = $event->sendOutputToTelegram();

        $this->assertSame($event, $result);
    }

    /** @test */
    public function it_accepts_string_env_values_for_enabled_flag()
    {
        config()->set('schedule-telegram-output.enabled', 'false');
        $this->assertFalse(TelegramNotifier::isEnabled());

        config()->set('schedule-telegram-output.enabled', '0');
        $this->assertFalse(TelegramNotifier::isEnabled());

        config()->set('schedule-telegram-output.enabled', 'true');
        $this->assertTrue(TelegramNotifier::isEnabled());
    }

    /** @test */
    public function it_skips_sending_when_send_message_is_called_while_disabled()
    {
        Http::fake();

        config()->set('schedule-telegram-output.enabled', false);
        config()->set('schedule-telegram-output.bots.default.token', 'real-token-value');

        TelegramNotifier::sendMessage('123456789', 'some output', 'app:demo');

        Http::assertNothingSent();
    }

    /** @test */
    public function it_skips_sending_when_send_message_has_placeholder_credentials()
    {
        Http::fake();

        config()->set('schedule-telegram-output.bots.default.token', 'dummy');
        config()->set('schedule-telegram-output.strict_mode', false);

        TelegramNotifier::sendMessage('dummy', 'some output', 'app:demo');

        Http::assertNothingSent();
    }
}
