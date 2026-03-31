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
    | Retry Configuration
    |--------------------------------------------------------------------------
    |
    | Configure retry behavior for failed Telegram API requests.
    |
    */
    'retry_attempts' => env('SCHEDULE_TELEGRAM_OUTPUT_RETRY_ATTEMPTS', 3),
    'retry_delay' => env('SCHEDULE_TELEGRAM_OUTPUT_RETRY_DELAY', 1000), // milliseconds
    'timeout' => env('SCHEDULE_TELEGRAM_OUTPUT_TIMEOUT', 30), // seconds
]; 