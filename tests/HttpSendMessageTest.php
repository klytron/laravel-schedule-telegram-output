<?php

namespace Klytron\LaravelScheduleTelegramOutput\Tests;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Klytron\LaravelScheduleTelegramOutput\TelegramNotifier;

class HttpSendMessageTest extends TestCase
{
    /** @test */
    public function it_posts_to_telegram_with_expected_url_and_payload_markdown()
    {
        // Arrange: configure token and parse mode
        config([
            'schedule-telegram-output.bots.default.token' => 'test-token',
            'schedule-telegram-output.default_chat_id' => '123456789',
            'schedule-telegram-output.message_format.parse_mode' => 'MarkdownV2',
            'schedule-telegram-output.message_format.snippet_max_length' => 500,
            'schedule-telegram-output.debug' => true,
        ]);

        // Fake Telegram API
        Http::fake([
            'https://api.telegram.org/*' => Http::response(['ok' => true], 200),
        ]);

        // Act
        $output = "Processing done\nAll good";
        $command = 'app:demo';
        TelegramNotifier::sendMessage('123456789', $output, $command);

        // Assert
        Http::assertSent(function (Request $request) {
            $urlOk = $request->url() === 'https://api.telegram.org/bottest-token/sendMessage';
            $json = $request->data(); // JSON payload since we post JSON
            return $urlOk
                && ($json['chat_id'] ?? null) === '123456789'
                && ($json['parse_mode'] ?? null) === 'MarkdownV2'
                && isset($json['text'])
                && is_string($json['text'])
                && str_contains($json['text'], 'Scheduled Job Output');
        });
    }

    /** @test */
    public function it_posts_html_message_when_parse_mode_is_html()
    {
        config([
            'schedule-telegram-output.bots.default.token' => 'test-token',
            'schedule-telegram-output.default_chat_id' => '999999',
            'schedule-telegram-output.message_format.parse_mode' => 'HTML',
            'schedule-telegram-output.message_format.snippet_max_length' => 500,
        ]);

        Http::fake([
            'https://api.telegram.org/*' => Http::response(['ok' => true], 200),
        ]);

        TelegramNotifier::sendMessage('999999', "Line 1\nLine 2", 'app:html-demo');

        Http::assertSent(function (Request $request) {
            $json = $request->data();
            return $request->url() === 'https://api.telegram.org/bottest-token/sendMessage'
                && ($json['chat_id'] ?? null) === '999999'
                && ($json['parse_mode'] ?? null) === 'HTML'
                && isset($json['text'])
                && str_contains($json['text'], '<b>🤖 Scheduled Job Output</b>')
                && str_contains($json['text'], '<pre>');
        });
    }

    /** @test */
    public function it_truncates_output_and_enforces_max_length()
    {
        config([
            'schedule-telegram-output.bots.default.token' => 'test-token',
            'schedule-telegram-output.default_chat_id' => '777777',
            'schedule-telegram-output.message_format.parse_mode' => 'MarkdownV2',
            // Force very small snippet and overall max length
            'schedule-telegram-output.message_format.snippet_max_length' => 20,
            'schedule-telegram-output.message_format.max_length' => 120,
        ]);

        Http::fake([
            'https://api.telegram.org/*' => Http::response(['ok' => true], 200),
        ]);

        $longOutput = implode("\n", array_map(fn($i) => "Line $i: Lorem ipsum dolor sit amet.", range(1, 50)));
        TelegramNotifier::sendMessage('777777', $longOutput, 'app:truncate-demo');

        Http::assertSent(function (Request $request) {
            $json = $request->data();
            $text = (string) ($json['text'] ?? '');
            return $request->url() === 'https://api.telegram.org/bottest-token/sendMessage'
                && ($json['chat_id'] ?? null) === '777777'
                && ($json['parse_mode'] ?? null) === 'MarkdownV2'
                && strlen($text) <= 120
                && str_contains($text, '[Output truncated: showing only a snippet]');
        });
    }
}
