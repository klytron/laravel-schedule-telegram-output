<?php

namespace Klytron\LaravelScheduleTelegramOutput\Tests;

use Illuminate\Console\Scheduling\Event;

class MacroRegistrationTest extends TestCase
{
    /** @test */
    public function it_registers_send_output_to_telegram_macro()
    {
        $this->assertTrue(
            Event::hasMacro('sendOutputToTelegram'),
            'sendOutputToTelegram macro should be registered on Event'
        );
    }
} 