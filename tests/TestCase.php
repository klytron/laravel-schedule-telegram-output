<?php

namespace Klytron\LaravelScheduleTelegramOutput\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Klytron\LaravelScheduleTelegramOutput\ScheduleTelegramOutputServiceProvider;

class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [ScheduleTelegramOutputServiceProvider::class];
    }
} 