# Laravel Schedule Telegram Output

[![Latest Version on Packagist](https://img.shields.io/packagist/v/klytron/laravel-schedule-telegram-output.svg?style=flat-square)](https://packagist.org/packages/klytron/laravel-schedule-telegram-output)
[![Total Downloads](https://img.shields.io/packagist/dt/klytron/laravel-schedule-telegram-output.svg?style=flat-square)](https://packagist.org/packages/klytron/laravel-schedule-telegram-output)
[![License](https://img.shields.io/packagist/l/klytron/laravel-schedule-telegram-output.svg?style=flat-square)](https://packagist.org/packages/klytron/laravel-schedule-telegram-output)
[![GitHub Stars](https://img.shields.io/github/stars/klytron/laravel-schedule-telegram-output?style=flat-square)](https://github.com/klytron/laravel-schedule-telegram-output/stargazers)

A Laravel package to send scheduled job outputs to Telegram with robust formatting, execution duration and exit code metrics, and flexible configuration.

---

## ✨ Features

- 📢 **Instant Notifications**: Send scheduled console output directly to Telegram channels, groups, or DMs
- ⏱️ **Execution Metrics**: Every notification ends with a single-line signature footer (`▶ php artisan cmd — exit code — duration — env`)
- 🛡️ **Dev & CI Safe (Graceful Degradation)**: Silently no-ops in local development or CI if credentials are missing, placeholders (`dummy`, `...`), or when `SCHEDULE_TELEGRAM_OUTPUT_ENABLED=false`, preventing unexpected scheduler crashes
- 🎯 **Macro-First Ergonomics**: Works directly on native `$schedule->command(...)->sendOutputToTelegram()`
- 🧼 **Robust Escaping**: Bulletproof MarkdownV2 escaping and HTML formatting preventing Telegram API parse errors
- 🔄 **Chunking & Retries**: Automatically chunks long outputs and retries transient network errors within a global runtime cap (fail-the-report-not-the-task by default, opt-in fail-hard)
- 🚀 **Multi-Laravel Support**: Fully compatible with Laravel 10.x, 11.x, 12.x, and 13.x

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
   SCHEDULE_TELEGRAM_OUTPUT_DEBUG=false
   SCHEDULE_TELEGRAM_OUTPUT_PARSE_MODE=MarkdownV2 # or HTML

   # Master kill switch (optional, default: true)
   # When false, all Telegram sending is skipped silently — checked before
   # anything else, so it always wins over strict mode.
   SCHEDULE_TELEGRAM_OUTPUT_ENABLED=true

   # Dev / CI Behavior (optional)
   # When false (default), missing credentials safely no-op without failing schedule:run
   # Placeholder values (dummy, placeholder, example, your-telegram-bot-token, ...)
   # are treated the same as missing credentials.
   # Set to true to throw a LogicException if credentials are missing
   SCHEDULE_TELEGRAM_OUTPUT_STRICT_MODE=false

   # Retry configuration (optional, see "Timeout, retries & failure mode" below)
   SCHEDULE_TELEGRAM_OUTPUT_RETRY_ATTEMPTS=3
   SCHEDULE_TELEGRAM_OUTPUT_RETRY_DELAY=1000
   SCHEDULE_TELEGRAM_OUTPUT_TIMEOUT=30
   SCHEDULE_TELEGRAM_OUTPUT_MAX_TOTAL_TIME=60
   SCHEDULE_TELEGRAM_OUTPUT_FAIL_HARD=false

   # Short environment label used in the report footer (optional, default: APP_ENV)
   SCHEDULE_TELEGRAM_OUTPUT_ENV_LABEL=prod
   ```

   See [Telegram Setup Guide](docs/TELEGRAM_SETUP.md) for details.
   
   Notes:
   - `SCHEDULE_TELEGRAM_OUTPUT_PARSE_MODE` is read by `config/schedule-telegram-output.php`.
   - By default only a snippet of output is sent (first 10 lines, up to 500 chars). Configure via `message_format.snippet_max_length`.
4. **Basic usage (macro-first):**

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

### Advanced (optional)

- The package includes advanced classes (`TelegramEvent`, `TelegramSchedule`, `TelegramScheduleTrait`) for special cases.
- The recommended approach is using the macro on `Illuminate\Console\Scheduling\Event` as shown above.

---

## ⏱️ Timeout, retries & failure mode

Every report ends with a single-line signature footer carrying identity/result metadata:

```
▶ php artisan app:process-uploads — exit 1 — 42s — prod
```

(signature, exit code, duration, environment label from `SCHEDULE_TELEGRAM_OUTPUT_ENV_LABEL` or `APP_ENV`).

HTTP behavior for Telegram API calls (all configurable via `config/schedule-telegram-output.php`):

| Option | Env var | Default | Meaning |
|---|---|---|---|
| `timeout` | `SCHEDULE_TELEGRAM_OUTPUT_TIMEOUT` | `30` | Per-request HTTP timeout, in seconds. Shrunk automatically when less budget remains. |
| `retry_attempts` | `SCHEDULE_TELEGRAM_OUTPUT_RETRY_ATTEMPTS` | `3` | Max HTTP attempts per report. |
| `retry_delay` | `SCHEDULE_TELEGRAM_OUTPUT_RETRY_DELAY` | `1000` | Base delay between attempts, in milliseconds, with linear backoff (`delay × attempt`). Capped by the remaining budget. |
| `max_total_time` | `SCHEDULE_TELEGRAM_OUTPUT_MAX_TOTAL_TIME` | `60` | Global runtime cap per report, in seconds. A Telegram outage can never extend a scheduled task's runtime beyond this budget (retries + backoff included). |
| `fail_hard` | `SCHEDULE_TELEGRAM_OUTPUT_FAIL_HARD` | `false` | When `false` (default), a failed report is logged and the task continues (fail the *report*, not the task). When `true`, a `RuntimeException` is thrown after retries are exhausted so the task itself fails. |

> Note: the full guides live in `docs/` (excluded from the Composer dist package via
> `.gitattributes`); this table is duplicated here in the README so the defaults
> always ship with the distribution.

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
