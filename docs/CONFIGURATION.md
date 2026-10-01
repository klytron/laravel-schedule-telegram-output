# Configuration Reference

This document describes all configuration options available in `config/schedule-telegram-output.php`.

## Bots

- **default_bot**: The default bot name (default: `default`).
- **bots**: Array of bot configurations. Each bot can have:
  - `token`: The Telegram bot token (required)
  - `certificate_path`: Path to certificate (optional)
  - `webhook_url`: Webhook URL (optional)
  - `commands`: Array of custom bot commands (optional)

## Default Chat ID

- **default_chat_id**: The default chat ID to send messages to. Can be overridden per command.

## Kill Switch

- **enabled** (`SCHEDULE_TELEGRAM_OUTPUT_ENABLED`, default: `true`): master on/off switch. When `false`, all Telegram sending is skipped silently — checked before anything else, so it always wins over `strict_mode`.

## Placeholder Credentials

Token/chat ID values matching obvious placeholders are treated the same as missing
credentials (graceful no-op, or `LogicException` in `strict_mode`): `dummy`,
`placeholder`, `example`, `changeme`, `your-telegram-bot-token`, `your-chat-id`,
and any value containing `dummy`/`placeholder`/`example`/`your-` (case-insensitive).

## Strict Mode

- **strict_mode** (`SCHEDULE_TELEGRAM_OUTPUT_STRICT_MODE`, default: `false`): when `true`, missing/placeholder credentials throw a `LogicException` instead of no-op.

## Environment Label

- **environment_label** (`SCHEDULE_TELEGRAM_OUTPUT_ENV_LABEL`, default: `APP_ENV`): short label identifying the environment in the single-line report footer (e.g. `prod`). Falls back to `app.env` when empty.

## Debug Logging

- **debug**: If true, logs detailed debug info for Telegram message sending. Defaults to `APP_DEBUG`.

## Message Format

- **message_format**: Array of options for message formatting:
  - `parse_mode`: `MarkdownV2` (default) or `HTML`.
  - `max_length`: Maximum message length (default: 4000).
  - `include_timestamp`: Whether to include the time in the message (default: true).
  - `include_command_name`: Whether to include the command name (default: true).
  - `show_url`: Whether to show the app URL in the message (default: false).
  - `snippet_max_length`: Max number of characters to send as output snippet (default: 500).

Every report ends with a single-line signature footer:

```
▶ php artisan app:process-uploads — exit 1 — 42s — prod
```

(signature, exit code, duration, environment label). The footer is always rendered;
exit code and duration are included when available.

## Timeout, Retries & Failure Mode

| Option | Env var | Default | Meaning |
|---|---|---|---|
| `timeout` | `SCHEDULE_TELEGRAM_OUTPUT_TIMEOUT` | `30` | Per-request HTTP timeout, in seconds. Shrunk automatically when less budget remains. |
| `retry_attempts` | `SCHEDULE_TELEGRAM_OUTPUT_RETRY_ATTEMPTS` | `3` | Max HTTP attempts per report. |
| `retry_delay` | `SCHEDULE_TELEGRAM_OUTPUT_RETRY_DELAY` | `1000` | Base delay between attempts, in milliseconds, with linear backoff (`delay × attempt`). Capped by the remaining budget. |
| `max_total_time` | `SCHEDULE_TELEGRAM_OUTPUT_MAX_TOTAL_TIME` | `60` | Global runtime cap per report, in seconds (retries + backoff included). |
| `fail_hard` | `SCHEDULE_TELEGRAM_OUTPUT_FAIL_HARD` | `false` | When `true`, a `RuntimeException` is thrown after retries are exhausted so the task itself fails. Default `false`: fail the *report*, not the task.

## Example

```php
return [
    'default_bot' => env('TELEGRAM_BOT_NAME', 'default'),
    'bots' => [
        'default' => [
            'token' => env('TELEGRAM_BOT_TOKEN'),
            'certificate_path' => env('TELEGRAM_CERTIFICATE_PATH', ''),
            'webhook_url' => env('TELEGRAM_WEBHOOK_URL', ''),
            'commands' => [],
        ],
    ],
    'default_chat_id' => env('TELEGRAM_DEFAULT_CHAT_ID'),
    'enabled' => env('SCHEDULE_TELEGRAM_OUTPUT_ENABLED', true),
    'environment_label' => env('SCHEDULE_TELEGRAM_OUTPUT_ENV_LABEL'), // falls back to APP_ENV at runtime
    'debug' => env('SCHEDULE_TELEGRAM_OUTPUT_DEBUG', env('APP_DEBUG', false)),
    'message_format' => [
        'parse_mode' => 'MarkdownV2',
        'max_length' => 4000,
        'include_timestamp' => true,
        'include_command_name' => true,
        'show_url' => false,
        'snippet_max_length' => 500,
    ],
    'retry_attempts' => env('SCHEDULE_TELEGRAM_OUTPUT_RETRY_ATTEMPTS', 3),
    'retry_delay' => env('SCHEDULE_TELEGRAM_OUTPUT_RETRY_DELAY', 1000),
    'timeout' => env('SCHEDULE_TELEGRAM_OUTPUT_TIMEOUT', 30),
    'max_total_time' => env('SCHEDULE_TELEGRAM_OUTPUT_MAX_TOTAL_TIME', 60),
    'fail_hard' => env('SCHEDULE_TELEGRAM_OUTPUT_FAIL_HARD', false),
    'strict_mode' => env('SCHEDULE_TELEGRAM_OUTPUT_STRICT_MODE', false),
];
```

---

For more, see the [Getting Started Guide](GETTING_STARTED.md) and the main [README](../README.md).
