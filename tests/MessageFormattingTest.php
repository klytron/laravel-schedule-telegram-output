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
        $this->assertStringNotContainsString('\\\\.', $escaped);
        $this->assertStringNotContainsString('\\\\!', $escaped);
    }

    /** @test */
    public function it_escapes_real_world_failing_case()
    {
        $project = 'picture-gallery-adx-redirector';
        $env = 'production';
        config()->set('app.name', $project);
        config()->set('app.env', $env);
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

    /** @test */
    public function it_formats_metadata_footer_in_markdownv2()
    {
        $output = "Job completed successfully";
        $command = "reports:generate";
        $metadata = [
            'exit_code' => 0,
            'duration' => '1.25s',
        ];

        $messages = TelegramNotifier::formatMessage($output, $command, 'MarkdownV2', 4000, $metadata);
        $this->assertNotEmpty($messages);
        // Single-line signature footer: signature — exit code — duration — env
        $this->assertStringContainsString("\u{25B6} php artisan reports:generate \u{2014} exit 0 \u{2014} 1\.25s", $messages[0]);
    }

    /** @test */
    public function it_formats_metadata_footer_in_html()
    {
        $output = "Job completed successfully";
        $command = "reports:generate";
        $metadata = [
            'exit_code' => 1,
            'duration' => '3.50s',
        ];

        $messages = TelegramNotifier::formatMessage($output, $command, 'html', 4000, $metadata);
        $this->assertNotEmpty($messages);
        $this->assertStringContainsString("\u{25B6} php artisan reports:generate \u{2014} exit 1 \u{2014} 3.50s", $messages[0]);
    }

    /** @test */
    public function it_always_includes_signature_footer_even_without_metadata()
    {
        config()->set('app.env', 'production');

        [$contents] = TelegramNotifier::formatMessage("done", 'app:demo', 'MarkdownV2', 4000);
        $this->assertStringContainsString("\u{25B6} php artisan app:demo", $contents);
        $this->assertStringContainsString("production", $contents);
    }

    /** @test */
    public function it_uses_custom_signature_and_env_label_in_footer()
    {
        $metadata = [
            'signature' => 'php artisan app:process-uploads',
            'exit_code' => 1,
            'duration' => '42s',
            'env' => 'prod',
        ];

        [$contents] = TelegramNotifier::formatMessage("boom", 'app:process-uploads', 'MarkdownV2', 4000, $metadata);
        $this->assertStringContainsString("\u{25B6} php artisan app:process\-uploads \u{2014} exit 1 \u{2014} 42s \u{2014} prod", $contents);
    }
} 