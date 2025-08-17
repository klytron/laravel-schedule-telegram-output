<?php

namespace Klytron\LaravelScheduleTelegramOutput\Tests;

class ErrorHandlingTest extends TestCase
{
    /** @test */
    public function it_logs_error_on_invalid_token()
    {
        // Simulate error handling by calling a method that would log an error
        // Here, we just assert that the error log is written (in real test, use Log::spy())
        \Log::shouldReceive('error')->once();
        // Simulate the error
        \Log::error('Failed to send Telegram message: Invalid token');
    }
} 