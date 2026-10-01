<?php

namespace Klytron\LaravelScheduleTelegramOutput\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Illuminate\Console\Scheduling\CacheEventMutex;
use Illuminate\Console\Scheduling\EventMutex;
use Klytron\LaravelScheduleTelegramOutput\ScheduleTelegramOutputServiceProvider;

class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [ScheduleTelegramOutputServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Allow resolving scheduling Events directly in tests.
        $app->bind(EventMutex::class, CacheEventMutex::class);
    }
} 