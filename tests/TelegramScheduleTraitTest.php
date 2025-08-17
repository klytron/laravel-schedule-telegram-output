<?php

namespace Klytron\LaravelScheduleTelegramOutput\Tests;

use Klytron\LaravelScheduleTelegramOutput\TelegramScheduleTrait;
use Illuminate\Console\Scheduling\Event;

class TelegramScheduleTraitTest extends TestCase
{
    use TelegramScheduleTrait;

    /** @test */
    public function it_can_add_output_to_telegram()
    {
        $event = $this->app->make(Event::class, ['command' => 'test:command']);
        $result = $this->addOutputToTelegram($event, '123456');
        $this->assertInstanceOf(Event::class, $result);
    }
} 