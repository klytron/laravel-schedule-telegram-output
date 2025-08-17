<?php

namespace Klytron\LaravelScheduleTelegramOutput;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Console\Scheduling\Event;
use Klytron\LaravelScheduleTelegramOutput\TelegramNotifier;

class ScheduleTelegramOutputServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/schedule-telegram-output.php', 'schedule-telegram-output'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/schedule-telegram-output.php' => config_path('schedule-telegram-output.php'),
            ], 'schedule-telegram-output-config');

            $this->commands([
                // Add any console commands here if needed
            ]);
        }

        // Add macro to Event class to support telegram output
        Event::macro('sendOutputToTelegram', function ($chatId = null) {
            // Always call sendOutputTo to ensure output is captured
            $this->sendOutputTo(app('path.storage').'/logs/schedule-telegram-'.sha1($this->command).'.log');

            $chatId = $chatId ?? config('schedule-telegram-output.default_chat_id');
            if (!$chatId) {
                throw new \LogicException('Chat ID is required. Either pass it to sendOutputToTelegram() or set TELEGRAM_DEFAULT_CHAT_ID in your .env file.');
            }

            return $this->then(function () use ($chatId) {
                $output = is_file($this->output) ? file_get_contents($this->output) : '';
                if (empty($output)) {
                    return;
                }
                try {
                    // Extract clean command name (remove full path)
                    $commandName = $this->command;
                    if (str_contains($commandName, 'artisan')) {
                        $parts = explode(' ', $commandName);
                        $commandName = end($parts); // Get the last part (the actual command)
                    }
                    
                    TelegramNotifier::sendMessage($chatId, $output, $commandName);
                    return;
                } catch (\Exception $e) {
                    \Log::error('Failed to send Telegram message: ' . $e->getMessage());
                }
            });
        });
    }
} 