# Changelog

All notable changes to `laravel-schedule-telegram-output` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Placeholder-credential detection: obvious placeholder token/chat ID values (`dummy`, `placeholder`, `example`, `your-telegram-bot-token`, ...) are treated the same as missing credentials (graceful no-op, or `LogicException` in `strict_mode`).
- `enabled` configuration option (`SCHEDULE_TELEGRAM_OUTPUT_ENABLED`, default `true`) as a hard kill switch — checked before anything else, always wins over `strict_mode`.
- Single-line signature report footer (`▶ php artisan cmd — exit code — duration — env`), always rendered, with configurable `environment_label` (`SCHEDULE_TELEGRAM_OUTPUT_ENV_LABEL`, defaults to `APP_ENV`).
- `max_total_time` configuration option (`SCHEDULE_TELEGRAM_OUTPUT_MAX_TOTAL_TIME`, default `60` seconds) as a global runtime cap per report — a Telegram outage can never extend a scheduled task's runtime beyond it.
- `fail_hard` opt-in configuration (`SCHEDULE_TELEGRAM_OUTPUT_FAIL_HARD`, default `false`): when `true`, a `RuntimeException` is thrown after retries are exhausted so the task itself fails (default still fails the *report*, not the task).
- Timeout/retry defaults table duplicated into `README.md` so the semantics always ship inside the Composer dist package (`docs/` is export-ignored).

### Changed
- Legacy `TelegramEvent::sendOutputToTelegram()` path now mirrors the macro's graceful no-op behavior (missing/placeholder credentials, kill switch, `strict_mode`) and passes exit code/duration/signature metadata so legacy reports get footers too.

## [1.3.0] - 2026-10-01

### Added
- Graceful no-op mode when `TELEGRAM_BOT_TOKEN` or `TELEGRAM_DEFAULT_CHAT_ID` are missing, preventing console artisan command crashes in testing and local development.
- `strict_mode` configuration option (`SCHEDULE_TELEGRAM_OUTPUT_STRICT_MODE`) to re-enable strict exception throwing if desired.
- Report footer metadata including command execution duration and exit code (`Exit Code: 0 | Duration: 1.24s`).
- Explicit `"illuminate/http": "^10.0|^11.0|^12.0|^13.0"` dependency in `composer.json`.
- `.gitattributes` to exclude tests, docs, and build configurations from distribution packages.

### Changed
- Broadened exception catching in the `sendOutputToTelegram` macro from `\Exception` to `\Throwable`.

### Deprecated
- `TelegramConsoleKernel` trait has been deprecated and will be removed in v2.0.0.
- `TelegramSchedule` class has been deprecated in favor of the standard `Event::macro('sendOutputToTelegram')` on Laravel's native scheduler.

### Removed
- Removed committed `composer.lock` and local dev `.ddev/` directory from the repository.

## [1.2.1] - 2026-03-31

### Fixed
- Fixed message length limit enforcement and truncation indicators.

## [1.2.0] - 2026-03-31

### Added
- Support for Laravel 10, 11, 12, and 13.
- HTML and MarkdownV2 message formatting modes.
- Configurable retry attempts, backoff delay, and HTTP request timeout.

## [1.1.0] - 2025-08-15

### Added
- `sendOutputToTelegram()` macro on `Illuminate\Console\Scheduling\Event`.
- Automatic log file capture for command output.

## [1.0.0] - 2025-07-20

### Added
- Initial release with Telegram bot notification for scheduled Laravel commands.
