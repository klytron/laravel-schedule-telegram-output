<?php

namespace Klytron\LaravelScheduleTelegramOutput;

use Illuminate\Console\Scheduling\Event;

trait TelegramScheduleTrait
{
    /**
     * Add telegram output to a scheduled event.
     *
     * @param  \Illuminate\Console\Scheduling\Event  $event
     * @param  int|null  $chatId
     * @return \Illuminate\Console\Scheduling\Event
     */
    public function addOutputToTelegram(Event $event, $chatId = null)
    {
        // Use the macro registered on Illuminate Console Event for consistency
        return $event->sendOutputToTelegram($chatId);
    }
} 