<?php

namespace Klytron\LaravelScheduleTelegramOutput\Tests;

use Illuminate\Console\Scheduling\Event;

class MacroRegistrationTest extends TestCase
{
    /** @test */
    public function it_registers_send_output_to_telegram_macro()
    {
        $event = $this->app->make(Event::class, ['command' => 'inspire']);
        $this->assertTrue(
            method_exists($event, 'sendOutputToTelegram'),
            'sendOutputToTelegram macro should be registered on Event'
        );
    }
} 