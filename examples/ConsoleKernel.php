<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Klytron\LaravelScheduleTelegramOutput\TelegramScheduleTrait;

/**
 * Example Console Kernel showing how to use the Laravel Schedule Telegram Output package
 */
class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Example 1: Basic macro usage (default chat ID)
        $schedule->command('inspire')->sendOutputToTelegram();

        // Example 2: Macro usage with custom chat ID
        $schedule->command('backup:run')->sendOutputToTelegram('123456789');

        // Example 3: Using the TelegramScheduleTrait for programmatic/conditional logic
        $event = $schedule->command('custom:task');
        $this->addOutputToTelegram($event, '987654321');

        // Example 4: Conditional Telegram output (only on Mondays)
        $event2 = $schedule->command('weekly:report');
        if (now()->isMonday()) {
            $this->addOutputToTelegram($event2);
        }

        // Example 5: Advanced - multiple outputs
        $event3 = $schedule->command('complex:job');
        $event3->sendOutputToTelegram(); // Default chat
        $this->addOutputToTelegram($event3, '1122334455'); // Also send to another chat
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
} 