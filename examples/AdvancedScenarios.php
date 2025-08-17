<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Klytron\LaravelScheduleTelegramOutput\TelegramScheduleTrait;

class AdvancedScenarios
{
    use TelegramScheduleTrait;

    public function scheduleAdvanced(Schedule $schedule)
    {
        // 1. Send output to multiple Telegram bots (different tokens)
        $event = $schedule->command('multi:bot');
        $this->addOutputToTelegram($event, env('TELEGRAM_CHAT_ID_1'));
        // Switch bot token in config dynamically if needed
        // config(['schedule-telegram-output.bots.default.token' => env('TELEGRAM_BOT_TOKEN_2')]);
        // $this->addOutputToTelegram($event, env('TELEGRAM_CHAT_ID_2'));

        // 2. Custom message formatting per event (change parse_mode for this run)
        config(['schedule-telegram-output.message_format.parse_mode' => 'HTML']);
        $event2 = $schedule->command('html:report');
        $this->addOutputToTelegram($event2);
        // Reset to default after
        config(['schedule-telegram-output.message_format.parse_mode' => 'MarkdownV2']);

        // 3. Disable Telegram output for certain environments
        $event3 = $schedule->command('prod:only');
        if (app()->environment('production')) {
            $this->addOutputToTelegram($event3);
        }

        // 4. Use environment variables for dynamic chat/bot selection
        $event4 = $schedule->command('env:dynamic');
        $chatId = env('DYNAMIC_TELEGRAM_CHAT_ID', env('TELEGRAM_DEFAULT_CHAT_ID'));
        $this->addOutputToTelegram($event4, $chatId);

        // 5. Handle failures gracefully (try-catch around Telegram logic)
        try {
            $event5 = $schedule->command('may:fail');
            $this->addOutputToTelegram($event5);
        } catch (\Exception $e) {
            \Log::error('Failed to add Telegram output: ' . $e->getMessage());
        }
    }
} 