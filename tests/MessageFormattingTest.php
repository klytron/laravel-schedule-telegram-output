<?php

namespace Klytron\LaravelScheduleTelegramOutput\Tests;

use Klytron\LaravelScheduleTelegramOutput\TelegramNotifier;

class MessageFormattingTest extends TestCase
{
    /** @test */
    public function it_formats_markdownv2_messages_correctly()
    {
        $output = "Failed: The debug mode was expected to be `false`, but actually was `true`";
        $outputMd = TelegramNotifier::escapeMarkdownV2($output);
        $contents = "*🤖 Scheduled Job Output*\n\n";
        $contents .= "*Output:*\n" . $outputMd;
        $this->assertStringContainsString('*🤖 Scheduled Job Output*', $contents);
        $this->assertStringContainsString('Failed:', $contents);
        $this->assertStringContainsString('debug mode', $contents);
    }

    /** @test */
    public function it_formats_html_messages_correctly()
    {
        $output = "Failed: The debug mode was expected to be false, but actually was true";
        $outputHtml = e($output);
        $outputPre = '<pre>' . $outputHtml . '</pre>';
        $contents = "<b>🤖 Scheduled Job Output</b><br><br>";
        $contents .= "<b>Output:</b><br>" . $outputPre;
        $this->assertStringContainsString('<b>🤖 Scheduled Job Output</b>', $contents);
        $this->assertStringContainsString('<pre>', $contents);
        $this->assertStringContainsString('debug mode', $contents);
    }

    /** @test */
    public function it_escapes_dots_and_special_characters_in_markdownv2()
    {
        $output = "Visit example.com and see file my.file.txt!";
        $escaped = TelegramNotifier::escapeMarkdownV2($output);
        // Dots and exclamation marks must be escaped
        $this->assertStringContainsString('example\.com', $escaped);
        $this->assertStringContainsString('my\.file\.txt', $escaped);
        $this->assertStringContainsString('\!', $escaped);
        // No double escaping
        $this->assertStringNotContainsString('\\\.', $escaped);
        $this->assertStringNotContainsString('\\!', $escaped);
    }

    /** @test */
    public function it_escapes_real_world_failing_case()
    {
        $project = 'picture-gallery-adx-redirector';
        $env = 'production';
        $output = "Processing file: my.file.txt\nURL: https://example.com/path.to/file\nDone.";
        $command = 'app:process-uploaded-csv';
        $message = TelegramNotifier::formatMessage($output, $command, 'MarkdownV2', 4000)[0];
        // All dots must be escaped
        $this->assertStringContainsString('picture\-gallery\-adx\-redirector', $message);
        $this->assertStringContainsString('my\.file\.txt', $message);
        $this->assertStringContainsString('https://example\.com/path\.to/file', $message);
        $this->assertStringContainsString('Done\.', $message);
        // No double escaping
        $this->assertStringNotContainsString('\\\.', $message);
        $this->assertStringNotContainsString('\\\-', $message);
    }

    /** @test */
    public function it_escapes_minimal_dot_message()
    {
        $output = "test.example.com";
        $escaped = TelegramNotifier::escapeMarkdownV2($output);
        $this->assertSame('test\.example\.com', $escaped);
        // No double escaping
        $this->assertStringNotContainsString('\\\.', $escaped);
    }
} 