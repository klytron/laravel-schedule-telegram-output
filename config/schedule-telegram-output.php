<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Telegram Bot Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration is used by the schedule telegram output package.
    | The package communicates with Telegram Bot API directly via HTTP.
    |
    */

    'default_bot' => env('TELEGRAM_BOT_NAME', 'default'),

    'bots' => [
        'default' => [
            'token' => env('TELEGRAM_BOT_TOKEN'),
            'certificate_path' => env('TELEGRAM_CERTIFICATE_PATH', ''),
            'webhook_url' => env('TELEGRAM_WEBHOOK_URL', ''),
            'commands' => [
                // Define your bot commands here
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Chat ID
    |--------------------------------------------------------------------------
    |
    | Default chat ID to send scheduled command outputs to.
    | You can override this per command using sendOutputToTelegram().
    |
    */
    'default_chat_id' => env('TELEGRAM_DEFAULT_CHAT_ID'),

    /*
    |--------------------------------------------------------------------------
    | Debug Logging
    |--------------------------------------------------------------------------
    |
    | If true, logs detailed debug info for Telegram message sending.
    | Defaults to app.debug, but can be overridden here.
    |
    */
    'debug' => env('SCHEDULE_TELEGRAM_OUTPUT_DEBUG', env('APP_DEBUG', false)),

    /*
    |--------------------------------------------------------------------------
    | Message Format
    |--------------------------------------------------------------------------
    |
    | Configure how the telegram messages are formatted.
    |
    */
    'message_format' => [
        'parse_mode' => env('SCHEDULE_TELEGRAM_OUTPUT_PARSE_MODE', 'MarkdownV2'),
        'max_length' => 4000,
        'include_timestamp' => true,
        'include_command_name' => true,
        // Show the app URL in Telegram messages (default: false)
        'show_url' => false,
        // Max number of characters to send as output snippet (default: 500)
        'snippet_max_length' => 500,
    ],
    // Log the escaped message and HTTP payload (default: app.debug)
    'log_payload' => env('SCHEDULE_TELEGRAM_OUTPUT_LOG_PAYLOAD', env('APP_DEBUG', false)),

    /*
    |--------------------------------------------------------------------------
    | Kill Switch
    |--------------------------------------------------------------------------
    |
    | Master on/off switch. When false, all Telegram sending is skipped
    | silently (no-op), even if credentials are configured. This check runs
    | before strict_mode, so it always wins.
    |
    */
    'enabled' => env('SCHEDULE_TELEGRAM_OUTPUT_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Environment Label
    |--------------------------------------------------------------------------
    |
    | Short label identifying the environment in the single-line report footer
    | (e.g. "prod"). Falls back to app.env when empty.
    |
    */
    'environment_label' => env('SCHEDULE_TELEGRAM_OUTPUT_ENV_LABEL'),

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    |
    | Configure retry behavior for failed Telegram API requests.
    |
    | - retry_attempts: max HTTP attempts per report (default: 3).
    | - retry_delay: base delay between attempts in milliseconds, with linear
    |   backoff (delay * attempt). Capped by max_total_time (default: 1000).
    | - timeout: per-request HTTP timeout in seconds (default: 30).
    | - max_total_time: global runtime cap in seconds for a single report.
    |   A Telegram outage can never extend a scheduled task's runtime beyond
    |   this budget, including retries and backoff sleeps (default: 60).
    |
    */
    'retry_attempts' => env('SCHEDULE_TELEGRAM_OUTPUT_RETRY_ATTEMPTS', 3),
    'retry_delay' => env('SCHEDULE_TELEGRAM_OUTPUT_RETRY_DELAY', 1000), // milliseconds
    'timeout' => env('SCHEDULE_TELEGRAM_OUTPUT_TIMEOUT', 30), // seconds
    'max_total_time' => env('SCHEDULE_TELEGRAM_OUTPUT_MAX_TOTAL_TIME', 60), // seconds

    /*
    |--------------------------------------------------------------------------
    | Fail Hard (opt-in)
    |--------------------------------------------------------------------------
    |
    | When false (default), a failed report is logged and the scheduled task
    | continues unaffected (fail the *report*, not the task). When true,
    | a RuntimeException is thrown after retries are exhausted so the task
    | itself fails.
    |
    */
    'fail_hard' => env('SCHEDULE_TELEGRAM_OUTPUT_FAIL_HARD', false),

    /*
    |--------------------------------------------------------------------------
    | Strict Mode
    |--------------------------------------------------------------------------
    |
    | When true, missing Telegram credentials will throw a LogicException during
    | schedule definition. When false (default), missing credentials gracefully
    | no-op to prevent crashing artisan commands in local dev or CI.
    |
    */
    'strict_mode' => env('SCHEDULE_TELEGRAM_OUTPUT_STRICT_MODE', false),
]; 