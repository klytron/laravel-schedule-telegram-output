<?php

namespace Klytron\LaravelScheduleTelegramOutput\Tests;

use Illuminate\Support\Facades\Http;
use Klytron\LaravelScheduleTelegramOutput\TelegramNotifier;

class ResilienceTest extends TestCase
{
    protected function configureFailingApi(): void
    {
        config()->set('schedule-telegram-output.bots.default.token', 'test-token');
        config()->set('schedule-telegram-output.message_format.parse_mode', 'MarkdownV2');

        Http::fake([
            'https://api.telegram.org/*' => Http::response(['ok' => false], 500),
        ]);
    }

    /** @test */
    public function it_fails_the_report_not_the_task_by_default()
    {
        $this->configureFailingApi();
        config()->set('schedule-telegram-output.retry_attempts', 2);
        config()->set('schedule-telegram-output.retry_delay', 1);
        config()->set('schedule-telegram-output.fail_hard', false);

        // Must not throw: the scheduled task continues unaffected.
        TelegramNotifier::sendMessage('123456789', 'some output', 'app:demo');

        Http::assertSentCount(2);
    }

    /** @test */
    public function it_throws_in_fail_hard_mode_after_retries_are_exhausted()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to send Telegram message');

        $this->configureFailingApi();
        config()->set('schedule-telegram-output.retry_attempts', 2);
        config()->set('schedule-telegram-output.retry_delay', 1);
        config()->set('schedule-telegram-output.fail_hard', true);

        TelegramNotifier::sendMessage('123456789', 'some output', 'app:demo');
    }

    /** @test */
    public function it_accepts_string_env_values_for_fail_hard_flag()
    {
        config()->set('schedule-telegram-output.fail_hard', 'true');
        $this->assertTrue(TelegramNotifier::shouldFailHard());

        config()->set('schedule-telegram-output.fail_hard', '0');
        $this->assertFalse(TelegramNotifier::shouldFailHard());
    }

    /** @test */
    public function it_caps_total_runtime_during_an_outage()
    {
        $this->configureFailingApi();
        config()->set('schedule-telegram-output.retry_attempts', 10);
        config()->set('schedule-telegram-output.retry_delay', 5000);
        config()->set('schedule-telegram-output.timeout', 5);
        config()->set('schedule-telegram-output.max_total_time', 2);
        config()->set('schedule-telegram-output.fail_hard', false);

        $startedAt = microtime(true);
        TelegramNotifier::sendMessage('123456789', 'some output', 'app:demo');
        $elapsed = microtime(true) - $startedAt;

        // 2s budget + generous headroom for CI slowness; without the cap this
        // would sleep ~5s+ between attempts across 10 attempts.
        $this->assertLessThan(6, $elapsed);
        $this->assertLessThan(10, count(Http::recorded()));
    }
}
