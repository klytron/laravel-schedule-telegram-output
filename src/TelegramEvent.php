<?php

namespace Klytron\LaravelScheduleTelegramOutput;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Support\Facades\Log;
use Klytron\LaravelScheduleTelegramOutput\TelegramNotifier;

/**
 * Advanced/optional Event subclass.
 *
 * Prefer using the macro registered on Illuminate\Console\Scheduling\Event:
 *   $schedule->command('...')->sendOutputToTelegram();
 *
 * This class remains for specialized use-cases and is aligned to the macro's
 * output file naming for consistency.
 */
class TelegramEvent extends Event
{
    /**
     * Ensure that the command output is being captured.
     */
    protected function ensureOutputIsBeingCaptured(): void
    {
        if ($this->output == $this->getDefaultOutput()) {
            // Match the macro's file naming strategy so both paths are consistent
            $this->sendOutputTo(storage_path('logs/schedule-telegram-'.sha1($this->command).'.log'));
        }
    }

    /**
     * Send the captured output to Telegram.
     *
     * Mirrors the macro's graceful no-op behavior: missing/placeholder
     * credentials (or the `enabled` kill switch) silently skip sending
     * unless strict_mode is enabled.
     *
     * @param string|null $chatId
     * @return $this
     * @throws \LogicException
     */
    public function sendOutputToTelegram($chatId = null): self
    {
        $startTime = microtime(true);

        $this->ensureOutputIsBeingCaptured();

        // Hard kill switch: checked before anything else, always wins over strict_mode.
        if (!TelegramNotifier::isEnabled()) {
            if (config('schedule-telegram-output.debug', config('app.debug', false))) {
                Log::debug('[ScheduleTelegramOutput] telegram output disabled: no credentials');
            }

            return $this;
        }

        // Use provided chat ID or default from config
        $chatId = $chatId ?? config('schedule-telegram-output.default_chat_id');
        $botToken = config('schedule-telegram-output.bots.default.token');

        if (TelegramNotifier::isMissingOrPlaceholder($chatId) || TelegramNotifier::isMissingOrPlaceholder($botToken)) {
            if (config('schedule-telegram-output.strict_mode', false)) {
                throw new \LogicException('Telegram credentials missing. Set TELEGRAM_BOT_TOKEN and TELEGRAM_DEFAULT_CHAT_ID or pass chatId.');
            }

            if (config('schedule-telegram-output.debug', config('app.debug', false))) {
                Log::debug('[ScheduleTelegramOutput] telegram output disabled: no credentials');
            }

            return $this;
        }

        // Defer reading output until after the event runs
        return $this->then(function () use ($chatId, $startTime) {
            $text = '';
            if (is_file($this->output) && is_readable($this->output)) {
                $text = @file_get_contents($this->output);
                if ($text === false) {
                    Log::warning('[ScheduleTelegramOutput] Failed to read output file', ['output' => $this->output]);
                    $text = '';
                }
            }
            if (empty($text)) {
                return;
            }
            $duration = round(microtime(true) - $startTime, 2);
            $commandName = $this->command;
            if (str_contains($commandName, 'artisan')) {
                $parts = explode(' ', $commandName);
                $commandName = end($parts);
            }
            $this->sendTelegramMessage($chatId, $text, [
                'signature' => "php artisan {$commandName}",
                'duration' => "{$duration}s",
                'exit_code' => $this->exitCode ?? null,
            ]);
        });
    }

    /**
     * Format and send the message to Telegram via HTTP request.
     *
     * @param string $chatId
     * @param string $text
     * @param array $metadata
     */
    protected function sendTelegramMessage($chatId, $text, array $metadata = []): void
    {
        try {
            $commandName = $this->command;
            if (str_contains($commandName, 'artisan')) {
                $parts = explode(' ', $commandName);
                $commandName = end($parts);
            }

            TelegramNotifier::sendMessage($chatId, $text, $commandName, $metadata);
        } catch (\Throwable $e) {
            if (TelegramNotifier::shouldFailHard()) {
                throw $e;
            }

            // Log the error but don't fail the scheduled task
            Log::error('Failed to send Telegram message: ' . $e->getMessage());
        }
    }
}
