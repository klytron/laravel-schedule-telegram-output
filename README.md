# Laravel Schedule Telegram Output

[![Latest Version on Packagist](https://img.shields.io/packagist/v/klytron/laravel-schedule-telegram-output.svg?style=flat-square)](https://packagist.org/packages/klytron/laravel-schedule-telegram-output)
[![Total Downloads](https://img.shields.io/packagist/dt/klytron/laravel-schedule-telegram-output.svg?style=flat-square)](https://packagist.org/packages/klytron/laravel-schedule-telegram-output)
[![License](https://img.shields.io/packagist/l/klytron/laravel-schedule-telegram-output.svg?style=flat-square)](https://packagist.org/packages/klytron/laravel-schedule-telegram-output)
[![GitHub Stars](https://img.shields.io/github/stars/klytron/laravel-schedule-telegram-output?style=flat-square)](https://github.com/klytron/laravel-schedule-telegram-output/stargazers)

A Laravel package to send scheduled job outputs to Telegram with robust formatting and flexible configuration.

---

## 🚀 Quick Start

1. **Install:**

   ```bash
   composer require klytron/laravel-schedule-telegram-output
   ```

2. **Publish config (optional):**

   ```bash
   php artisan vendor:publish --provider="Klytron\LaravelScheduleTelegramOutput\ScheduleTelegramOutputServiceProvider" --tag=schedule-telegram-output-config
   ```

3. **Configure your `.env`:**

   ```env
   TELEGRAM_BOT_TOKEN=your-telegram-bot-token
   TELEGRAM_DEFAULT_CHAT_ID=your-chat-id
   SCHEDULE_TELEGRAM_OUTPUT_DEBUG=true # or false
   SCHEDULE_TELEGRAM_OUTPUT_PARSE_MODE=MarkdownV2 # or HTML
   ```

   See [Telegram Setup Guide](docs/TELEGRAM_SETUP.md) for details.
4. **Basic usage:**

   ```php
   $schedule->command('your:command')->sendOutputToTelegram();
   ```

   Or specify a chat ID:

   ```php
   $schedule->command('your:command')->sendOutputToTelegram('123456789');
   ```

---

## ⚙️ Configuration

- All options are in `config/schedule-telegram-output.php`.
- By default, only a snippet of the output (first 10 lines or up to 500 characters) is sent to Telegram.
- You can override the snippet length and other options in your config.
- See the [Configuration Reference](docs/CONFIGURATION.md) for all options and details.

---

## 📖 Documentation & Guides

- [Getting Started Guide](docs/GETTING_STARTED.md)
- [Telegram Setup Guide](docs/TELEGRAM_SETUP.md)
- [Configuration Reference](docs/CONFIGURATION.md)
- [Advanced Usage](docs/ADVANCED_USAGE.md)
- [Examples](docs/EXAMPLES.md)
- [Troubleshooting & FAQ](docs/TROUBLESHOOTING.md)

---

## 🧑‍💻 Advanced & Examples

- See [Examples](docs/EXAMPLES.md) and [Advanced Usage](docs/ADVANCED_USAGE.md) for trait-based, multi-bot, and conditional scenarios.

---

## ❓ Having issues?

- See [Troubleshooting & FAQ](docs/TROUBLESHOOTING.md)
- Or open an issue on [GitHub](https://github.com/klytron/laravel-schedule-telegram-output/issues)

---

## License

MIT
