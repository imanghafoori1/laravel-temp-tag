<?php

namespace Imanghafoori\TempTagTests;

use Imanghafoori\Tags\TempTagServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [TempTagServiceProvider::class];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadMigrationsFrom(__DIR__.'/Requirements/Database/Migrations');
    }
}
