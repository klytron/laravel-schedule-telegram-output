# Getting Started with Laravel Schedule Telegram Output

Welcome to the Laravel Schedule Telegram Output package! This guide will help you get up and running quickly.

## 1. Installation

Install the package via Composer:

```bash
composer require klytron/laravel-schedule-telegram-output
```

## 2. Publish the Configuration (Optional)

Publish the config file to customize settings:

```bash
php artisan vendor:publish --provider="Klytron\LaravelScheduleTelegramOutput\ScheduleTelegramOutputServiceProvider" --tag=schedule-telegram-output-config
```

## 3. Configure Your .env

Add the following to your `.env` file:

```
TELEGRAM_BOT_TOKEN=your-telegram-bot-token
TELEGRAM_DEFAULT_CHAT_ID=your-chat-id
SCHEDULE_TELEGRAM_OUTPUT_DEBUG=true # or false
SCHEDULE_TELEGRAM_OUTPUT_PARSE_MODE=MarkdownV2 # or HTML
```

See [How to get your Telegram Bot Token and Chat ID](TELEGRAM_SETUP.md) for details on obtaining these values.

## 4. Usage Example

In your `App\Console\Kernel` or any scheduled command:

```php
$schedule->command('your:command')->sendOutputToTelegram();
```

Or specify a chat ID:

```php
$schedule->command('your:command')->sendOutputToTelegram('123456789');
```

## 5. Advanced Usage

See the [README](../README.md) and the `examples/` folder for advanced scenarios, including trait-based usage and conditional notifications.

---

For more, see the [Telegram Setup Guide](TELEGRAM_SETUP.md).
