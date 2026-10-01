<?php

namespace Klytron\LaravelScheduleTelegramOutput;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotifier
{
    /**
     * Values that are treated the same as a missing credential.
     *
     * Catches the copy-pasted `.env.example` / CI placeholder values that
     * would otherwise fail at runtime (e.g. TELEGRAM_BOT_TOKEN=dummy).
     */
    protected static array $placeholderCredentials = [
        'dummy',
        'placeholder',
        'example',
        'test',
        'testing',
        'changeme',
        'change-me',
        'xxx',
        'todo',
        'null',
        'none',
        'nil',
        'undefined',
        'false',
        'your-telegram-bot-token',
        'your-chat-id',
        'your_bot_token',
        'your_chat_id',
        'replace-me',
        'replaceme',
    ];

    /**
     * Master kill switch. Accepts real booleans as well as common string
     * representations coming from env vars ("false", "0", "no", "off").
     */
    public static function isEnabled(): bool
    {
        return filter_var(config('schedule-telegram-output.enabled', true), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Whether fail-hard mode is enabled: throw after exhausted retries
     * instead of just logging the failed report.
     */
    public static function shouldFailHard(): bool
    {
        return filter_var(config('schedule-telegram-output.fail_hard', false), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Check whether a credential is missing or an obvious placeholder.
     */
    public static function isMissingOrPlaceholder($value): bool
    {
        $normalized = strtolower(trim((string) $value));

        if ($normalized === '' || $normalized === '0') {
            return true;
        }

        if (in_array($normalized, self::$placeholderCredentials, true)) {
            return true;
        }

        foreach (['dummy', 'placeholder', 'example', 'your-', 'changeme', 'replace'] as $needle) {
            if (str_contains($normalized, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Resolve the short environment label used in the report footer.
     */
    public static function environmentLabel(array $metadata = []): string
    {
        if (!empty($metadata['env'])) {
            return (string) $metadata['env'];
        }

        $label = config('schedule-telegram-output.environment_label');

        if (empty($label)) {
            $label = config('app.env', 'unknown');
        }

        return (string) $label;
    }

    /**
     * Build the single-line report footer:
     * signature — exit code — duration — env label.
     */
    public static function buildFooter(string $commandName, array $metadata = []): string
    {
        $signature = !empty($metadata['signature']) ? (string) $metadata['signature'] : "php artisan {$commandName}";

        $parts = ["\u{25B6} {$signature}"];

        if (isset($metadata['exit_code']) && $metadata['exit_code'] !== '') {
            $parts[] = "exit {$metadata['exit_code']}";
        }

        if (!empty($metadata['duration'])) {
            $parts[] = (string) $metadata['duration'];
        }

        $env = self::environmentLabel($metadata);

        if ($env !== '') {
            $parts[] = $env;
        }

        return implode(' — ', $parts);
    }
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
    public static function formatMessage($output, $commandName, $parseMode, $maxLength, array $metadata = [])
    {
        $truncated = false;
        $showUrl = config('schedule-telegram-output.message_format.show_url', false);
        $snippetMaxLength = config('schedule-telegram-output.message_format.snippet_max_length', 500);
        $includeTimestamp = (bool) config('schedule-telegram-output.message_format.include_timestamp', true);
        $includeCommandName = (bool) config('schedule-telegram-output.message_format.include_command_name', true);
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

        // Single-line signature footer: signature — exit code — duration — env.
        // Always rendered so every report carries identity/result metadata.
        $footer = self::buildFooter($commandName, $metadata);

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
            if ($includeCommandName) {
                $contents .= "<b>Command:</b> <code>" . e($commandName) . "</code><br>";
            }
            if ($includeTimestamp) {
                $contents .= "<b>Time:</b> " . e(now()->format('Y-m-d H:i:s T')) . "<br><br>";
            } else {
                $contents .= "<br>";
            }
            $contents .= "<b>Output:</b><br>" . $outputPre;
            $contents .= "<br><br>" . e($footer);
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
            if ($includeCommandName) {
                $contents .= "*Command:* `" . self::escapeMarkdownV2($commandName) . "`\n";
            }
            if ($includeTimestamp) {
                $contents .= "*Time:* " . self::escapeMarkdownV2(now()->format('Y-m-d H:i:s T')) . "\n\n";
            } else {
                $contents .= "\n";
            }
            $contents .= "*Output:*\n" . $outputMd;
            $contents .= "\n\n" . self::escapeMarkdownV2($footer);
        }
        // Enforce the maximum message length limit
        if (strlen($contents) > $maxLength) {
            $contents = substr($contents, 0, $maxLength - 10) . '...';
            $truncated = true;
        }
        return [$contents, $truncated];
    }

    /**
     * Send a message to Telegram
     */
    public static function sendMessage($chatId, $output, $commandName, array $metadata = [])
    {
        $shouldDebug = config('schedule-telegram-output.debug', config('app.debug'));

        if (!self::isEnabled()) {
            if ($shouldDebug) {
                Log::debug('[ScheduleTelegramOutput] telegram output disabled: no credentials');
            }

            return;
        }

        $maxLength = config('schedule-telegram-output.message_format.max_length', 4000);
        $parseMode = config('schedule-telegram-output.message_format.parse_mode', 'MarkdownV2');
        $botToken = config('schedule-telegram-output.bots.default.token');
        [$contents, $truncated] = self::formatMessage($output, $commandName, $parseMode, $maxLength, $metadata);
        $logPayload = config('schedule-telegram-output.log_payload', false);

        if (self::isMissingOrPlaceholder($botToken) || self::isMissingOrPlaceholder($chatId)) {
            if (config('schedule-telegram-output.strict_mode', false)) {
                throw new \LogicException('Telegram credentials missing. Set TELEGRAM_BOT_TOKEN and TELEGRAM_DEFAULT_CHAT_ID or pass chatId.');
            }

            if ($shouldDebug) {
                Log::debug('[ScheduleTelegramOutput] telegram output disabled: no credentials');
            }

            return;
        }

        // Prepare the payload for the HTTP request
        $payload = [
            'chat_id' => $chatId,
            'text' => $contents,
            'parse_mode' => $parseMode,
        ];

        if ($logPayload) {
            // Log the message immediately after escaping
            Log::debug('[ScheduleTelegramOutput] Escaped Telegram message', [
                'message' => $contents,
                'parse_mode' => $parseMode
            ]);
            // Log the actual HTTP payload (as array, not http_build_query)
            Log::debug('[ScheduleTelegramOutput] HTTP JSON payload', $payload);
        }
        if ($shouldDebug) {
            Log::info('[ScheduleTelegramOutput] Telegram message content', [
                'message' => $contents,
                'parse_mode' => $parseMode
            ]);
        }
        $apiUrl = "https://api.telegram.org/bot{$botToken}/sendMessage";
        
        // Configure retry attempts and timeout
        $maxRetries = max(1, (int) config('schedule-telegram-output.retry_attempts', 3));
        $retryDelay = max(0, (int) config('schedule-telegram-output.retry_delay', 1000)); // milliseconds
        $timeout = max(1, (int) config('schedule-telegram-output.timeout', 30)); // seconds
        // Global runtime cap: a Telegram outage must never extend a scheduled
        // task's runtime beyond this budget (retries + backoff included).
        $maxTotalTime = max(1, (float) config('schedule-telegram-output.max_total_time', 60)); // seconds

        $lastException = null;
        $startedAt = microtime(true);

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            $elapsed = microtime(true) - $startedAt;

            if ($elapsed >= $maxTotalTime) {
                break;
            }

            // Shrink the per-request timeout so this attempt cannot blow the remaining budget.
            $attemptTimeout = (int) max(1, min($timeout, $maxTotalTime - $elapsed));

            try {
                $response = Http::timeout($attemptTimeout)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post($apiUrl, $payload);

                if ($response->successful()) {
                    if ($shouldDebug) {
                        Log::info('[ScheduleTelegramOutput] Sent Telegram message chunk (HTTP)', [
                            'chat_id' => $chatId,
                            'length' => strlen($contents),
                            'truncated' => $truncated,
                            'parse_mode' => $parseMode,
                            'attempt' => $attempt
                        ]);
                    }
                    return; // Success, exit the retry loop
                }

                // If not successful, throw exception to trigger retry
                throw new \Exception("HTTP {$response->status()}: " . $response->body());

            } catch (\Exception $e) {
                $lastException = $e;

                if ($attempt < $maxRetries) {
                    if ($shouldDebug) {
                        Log::warning("[ScheduleTelegramOutput] Attempt {$attempt} failed, retrying...", [
                            'error' => $e->getMessage()
                        ]);
                    }
                    // Exponential backoff: wait longer between retries, capped by the remaining budget.
                    $remaining = $maxTotalTime - (microtime(true) - $startedAt);

                    if ($remaining <= 0) {
                        break;
                    }

                    usleep((int) (min($retryDelay * $attempt, $remaining * 1000) * 1000));
                }
            }
        }

        // All retries exhausted (or the runtime budget ran out)
        Log::error('[ScheduleTelegramOutput] Failed to send Telegram message after ' . $maxRetries . ' attempts', [
            'chat_id' => $chatId,
            'last_error' => $lastException ? $lastException->getMessage() : 'Unknown error',
            'parse_mode' => $parseMode
        ]);

        if (self::shouldFailHard()) {
            throw new \RuntimeException(
                '[ScheduleTelegramOutput] Failed to send Telegram message: ' . ($lastException ? $lastException->getMessage() : 'Unknown error'),
                0,
                $lastException
            );
        }
    }
} 