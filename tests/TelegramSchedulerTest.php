<?php

namespace Klytron\LaravelScheduleTelegramOutput\Tests;

use Orchestra\Testbench\TestCase;
use Illuminate\Console\Scheduling\Schedule;
use Klytron\LaravelScheduleTelegramOutput\ScheduleTelegramOutputServiceProvider;

class ScheduleTelegramOutputTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            ScheduleTelegramOutputServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Setup telegram configuration
        $app['config']->set('telegram.bots.default.token', 'test-token');
        $app['config']->set('schedule-telegram-output.default_chat_id', '1234567890');
        $app['config']->set('schedule-telegram-output.message_format.max_length', 4000);
        $app['config']->set('schedule-telegram-output.message_format.parse_mode', 'MarkdownV2');
    }

    /** @test */
    public function it_can_create_scheduled_command_with_telegram_output()
    {
        $schedule = app(Schedule::class);
        
        $event = $schedule->command('inspire')
            ->cron('* * * * *')
            ->sendOutputToTelegram(1234567890);

        $this->assertInstanceOf(\Illuminate\Console\Scheduling\Event::class, $event);
    }

    /** @test */
    public function it_has_send_output_to_telegram_macro_registered()
    {
        $this->assertTrue(\Illuminate\Console\Scheduling\Event::hasMacro('sendOutputToTelegram'));
    }
} 