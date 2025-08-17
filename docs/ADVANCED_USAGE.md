# Advanced Usage

This guide covers advanced scenarios for using Laravel Schedule Telegram Output.

## 1. Trait-Based Usage

Use the `TelegramScheduleTrait` for programmatic or conditional Telegram output:

```php
use Klytron\LaravelScheduleTelegramOutput\TelegramScheduleTrait;

class Kernel extends ConsoleKernel
{
    use TelegramScheduleTrait;

    protected function schedule(Schedule $schedule)
    {
        $event = $schedule->command('your:command');
        $this->addOutputToTelegram($event, '123456789'); // Optional: specify chat ID
    }
}
```

## 2. Conditional Notifications

Send Telegram output only under certain conditions:

```php
$event = $schedule->command('weekly:report');
if (now()->isMonday()) {
    $this->addOutputToTelegram($event);
}
```

## 3. Multi-Bot / Multi-Chat

Send output to multiple bots or chats:

```php
$event = $schedule->command('multi:bot');
$this->addOutputToTelegram($event, env('TELEGRAM_CHAT_ID_1'));
// Change bot token dynamically if needed
// config(['schedule-telegram-output.bots.default.token' => env('TELEGRAM_BOT_TOKEN_2')]);
// $this->addOutputToTelegram($event, env('TELEGRAM_CHAT_ID_2'));
```

## 4. Custom Formatting

Change the message format for a specific event:

```php
config(['schedule-telegram-output.message_format.parse_mode' => 'HTML']);
$event = $schedule->command('html:report');
$this->addOutputToTelegram($event);
// Reset to default after
config(['schedule-telegram-output.message_format.parse_mode' => 'MarkdownV2']);
```

---

For more, see the [examples/](../examples/) folder and the main [README](../README.md).
