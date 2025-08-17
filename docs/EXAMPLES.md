# Examples

Here are some common usage patterns for Laravel Schedule Telegram Output.

## 1. Basic Usage

```php
$schedule->command('your:command')->sendOutputToTelegram();
```

## 2. Custom Chat ID

```php
$schedule->command('your:command')->sendOutputToTelegram('123456789');
```

## 3. Trait-Based Usage

```php
use Klytron\LaravelScheduleTelegramOutput\TelegramScheduleTrait;

class Kernel extends ConsoleKernel
{
    use TelegramScheduleTrait;

    protected function schedule(Schedule $schedule)
    {
        $event = $schedule->command('your:command');
        $this->addOutputToTelegram($event, '123456789');
    }
}
```

## 4. Conditional Telegram Output

```php
$event = $schedule->command('weekly:report');
if (now()->isMonday()) {
    $this->addOutputToTelegram($event);
}
```

## 5. Multi-Bot / Multi-Chat

```php
$event = $schedule->command('multi:bot');
$this->addOutputToTelegram($event, env('TELEGRAM_CHAT_ID_1'));
// Change bot token dynamically if needed
// config(['schedule-telegram-output.bots.default.token' => env('TELEGRAM_BOT_TOKEN_2')]);
// $this->addOutputToTelegram($event, env('TELEGRAM_CHAT_ID_2'));
```

## 6. Custom Formatting

```php
config(['schedule-telegram-output.message_format.parse_mode' => 'HTML']);
$event = $schedule->command('html:report');
$this->addOutputToTelegram($event);
// Reset to default after
config(['schedule-telegram-output.message_format.parse_mode' => 'MarkdownV2']);
```

---

For more, see the [Getting Started Guide](GETTING_STARTED.md), [Advanced Usage](ADVANCED_USAGE.md), and the main [README](../README.md).
