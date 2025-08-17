<?php

namespace Klytron\LaravelScheduleTelegramOutput;

use Illuminate\Console\Scheduling\Event;
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
        if (is_null($this->output) || $this->output == $this->getDefaultOutput()) {
            // Match the macro's file naming strategy so both paths are consistent
            $this->sendOutputTo(storage_path('logs/schedule-telegram-'.sha1($this->command).'.log'));
        }
    }

    /**
     * Send the captured output to Telegram.
     *
     * @param string|null $chatId
     * @return $this
     * @throws \LogicException
     */
    public function sendOutputToTelegram($chatId = null): self
    {
        $this->ensureOutputIsBeingCaptured();

        // Use provided chat ID or default from config
        $chatId = $chatId ?? config('schedule-telegram-output.default_chat_id');

        if (!$chatId) {
            throw new \LogicException('Chat ID is required. Either pass it to sendOutputToTelegram() or set TELEGRAM_DEFAULT_CHAT_ID in your .env file.');
        }

        // Defer reading output until after the event runs
        return $this->then(function () use ($chatId) {
            $text = is_file($this->output) ? file_get_contents($this->output) : '';
            if (empty($text)) {
                return;
            }
            $this->sendTelegramMessage($chatId, $text);
        });
    }

    /**
     * Format and send the message to Telegram via HTTP request.
     *
     * @param string $chatId
     * @param string $text
     */
    protected function sendTelegramMessage($chatId, $text): void
    {
        try {
            $commandName = $this->command;
            if (str_contains($commandName, 'artisan')) {
                $parts = explode(' ', $commandName);
                $commandName = end($parts);
            }
            
            TelegramNotifier::sendMessage($chatId, $text, $commandName);
        } catch (\Exception $e) {
            // Log the error but don't fail the scheduled task
            \Log::error('Failed to send Telegram message: ' . $e->getMessage());
        }
    }
}
