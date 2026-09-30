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
            $startTime = microtime(true);

            // Always call sendOutputTo to ensure output is captured
            $this->sendOutputTo(storage_path('logs/schedule-telegram-'.sha1($this->command).'.log'));

            $resolvedChatId = $chatId ?? config('schedule-telegram-output.default_chat_id');
            $botToken = config('schedule-telegram-output.bots.default.token');

            // If credentials are not configured, allow graceful no-op in dev/CI unless strict mode is enabled
            if (empty($resolvedChatId) || empty($botToken)) {
                if (config('schedule-telegram-output.strict_mode', false)) {
                    throw new \LogicException('Telegram credentials missing. Set TELEGRAM_BOT_TOKEN and TELEGRAM_DEFAULT_CHAT_ID or pass chatId.');
                }

                if (config('schedule-telegram-output.debug', config('app.debug', false))) {
                    \Log::debug('[ScheduleTelegramOutput] sendOutputToTelegram skipped: Missing bot token or chat ID.');
                }

                return $this;
            }

            return $this->then(function () use ($resolvedChatId, $startTime) {
                $duration = round(microtime(true) - $startTime, 2);
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

                    $metadata = [
                        'duration' => "{$duration}s",
                        'exit_code' => $this->exitCode ?? null,
                    ];
                    
                    TelegramNotifier::sendMessage($resolvedChatId, $output, $commandName, $metadata);
                    return;
                } catch (\Throwable $e) {
                    \Log::error('Failed to send Telegram message: ' . $e->getMessage());
                }
            });
        });
    }
} 