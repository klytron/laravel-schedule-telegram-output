<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Klytron\LaravelScheduleTelegramOutput\TelegramScheduleTrait;

class TelegramTraitExample
{
    use TelegramScheduleTrait;

    public function scheduleCustom(Schedule $schedule)
    {
        // Example: Add Telegram output to a scheduled event dynamically
        $event = $schedule->command('dynamic:task');
        $chatId = $this->getDynamicChatId();
        $this->addOutputToTelegram($event, $chatId);
    }

    protected function getDynamicChatId()
    {
        // Example logic: choose chat ID based on environment or other logic
        return app()->environment('production') ? 'PROD_CHAT_ID' : 'DEV_CHAT_ID';
    }
} 