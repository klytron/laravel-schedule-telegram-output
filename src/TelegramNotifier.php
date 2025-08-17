<?php

namespace Klytron\LaravelScheduleTelegramOutput;

use Illuminate\Support\Facades\Http;

class TelegramNotifier
{
    /**
     * Escape text for Telegram MarkdownV2.
     * @see https://core.telegram.org/bots/api#markdownv2-style
     */
    public static function escapeMarkdownV2($text)
    {
        // List of special characters for Telegram MarkdownV2
        $specials = '_*[]()~`>#+-=|{}.!';
        // Only escape if not already escaped
        return preg_replace_callback('/(?<!\\\\)([' . preg_quote($specials, '/') . '])/', function ($m) {
            return '\\' . $m[1];
        }, $text);
    }

    /**
     * Format the message for Telegram (MarkdownV2 or HTML)
     */
    public static function formatMessage($output, $commandName, $parseMode, $maxLength)
    {
        $truncated = false;
        $showUrl = config('schedule-telegram-output.message_format.show_url', false);
        $snippetMaxLength = config('schedule-telegram-output.message_format.snippet_max_length', 500);
        // Only send a snippet of the output (first 10 lines or snippetMaxLength chars, whichever is shorter)
        $lines = preg_split('/\r?\n/', $output);
        $snippet = implode("\n", array_slice($lines, 0, 10));
        if (strlen($snippet) > $snippetMaxLength) {
            $snippet = substr($snippet, 0, $snippetMaxLength);
        }
        if (count($lines) > 10 || strlen($output) > strlen($snippet)) {
            $snippet .= "\n...\n[Output truncated: showing only a snippet]";
            $truncated = true;
        }
        if (strtolower($parseMode) === 'html') {
            $outputClean = str_replace('`', '', $snippet);
            $outputHtml = e($outputClean);
            $outputPre = '<pre>' . $outputHtml . '</pre>';
            $contents = "<b>🤖 Scheduled Job Output</b><br><br>";
            $contents .= "<b>Project:</b> " . e(config('app.name') ?: 'Laravel App') . "<br>";
            $contents .= "<b>Environment:</b> " . e(config('app.env') ?: 'unknown') . "<br>";
            if ($showUrl) {
                $contents .= "<b>URL:</b> " . e(config('app.url') ?: 'N/A') . "<br>";
            }
            $contents .= "<b>Server:</b> " . e(gethostname() ?: 'unknown') . "<br>";
            $contents .= "<b>Command:</b> <code>" . e($commandName) . "</code><br>";
            $contents .= "<b>Time:</b> " . e(now()->format('Y-m-d H:i:s T')) . "<br><br>";
            $contents .= "<b>Output:</b><br>" . $outputPre;
        } else {
            $outputClean = str_replace('`', '', $snippet);
            $outputMd = self::escapeMarkdownV2($outputClean);
            $contents = "*🤖 Scheduled Job Output*\n\n";
            $contents .= "*Project:* " . self::escapeMarkdownV2(config('app.name') ?: 'Laravel App') . "\n";
            $contents .= "*Environment:* " . self::escapeMarkdownV2(config('app.env') ?: 'unknown') . "\n";
            if ($showUrl) {
                $contents .= "*URL:* " . self::escapeMarkdownV2(config('app.url') ?: 'N/A') . "\n";
            }
            $contents .= "*Server:* " . self::escapeMarkdownV2(gethostname() ?: 'unknown') . "\n";
            $contents .= "*Command:* `" . self::escapeMarkdownV2($commandName) . "`\n";
            $contents .= "*Time:* " . self::escapeMarkdownV2(now()->format('Y-m-d H:i:s T')) . "\n\n";
            $contents .= "*Output:*\n" . $outputMd;
        }
        return [$contents, $truncated];
    }

    /**
     * Send a message to Telegram
     */
    public static function sendMessage($chatId, $output, $commandName)
    {
        $maxLength = config('schedule-telegram-output.message_format.max_length', 4000);
        $parseMode = config('schedule-telegram-output.message_format.parse_mode', 'MarkdownV2');
        $botToken = config('schedule-telegram-output.bots.default.token');
        [$contents, $truncated] = self::formatMessage($output, $commandName, $parseMode, $maxLength);
        $shouldDebug = config('schedule-telegram-output.debug', config('app.debug'));
        $logPayload = config('schedule-telegram-output.log_payload', false);

        // Prepare the payload for the HTTP request
        $payload = [
            'chat_id' => $chatId,
            'text' => $contents,
            'parse_mode' => $parseMode,
        ];

        if ($logPayload) {
            // Log the message immediately after escaping
            \Log::debug('[ScheduleTelegramOutput] Escaped Telegram message', [
                'message' => $contents,
                'parse_mode' => $parseMode
            ]);
            // Log the actual HTTP payload (as array, not http_build_query)
            \Log::debug('[ScheduleTelegramOutput] HTTP JSON payload', $payload);
        }
        if ($shouldDebug) {
            \Log::info('[ScheduleTelegramOutput] Telegram message content', [
                'message' => $contents,
                'parse_mode' => $parseMode
            ]);
        }
        $apiUrl = "https://api.telegram.org/bot{$botToken}/sendMessage";
        $response = Http::withHeaders(['Content-Type' => 'application/json'])->post($apiUrl, $payload);
        if (!$response->successful()) {
            \Log::error('[ScheduleTelegramOutput] Failed to send Telegram message', [
                'chat_id' => $chatId,
                'status' => $response->status(),
                'body' => $response->body(),
                'parse_mode' => $parseMode
            ]);
        } else if ($shouldDebug) {
            \Log::info('[ScheduleTelegramOutput] Sent Telegram message chunk (HTTP)', [
                'chat_id' => $chatId,
                'length' => strlen($contents),
                'truncated' => $truncated,
                'parse_mode' => $parseMode
            ]);
        }
    }
} 