<?php

namespace JiordiViera\LaravelTaskAutomator\Tests;

use JiordiViera\LaravelTaskAutomator\LaravelTaskAutomatorServiceProvider;
use \Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return[
            LaravelTaskAutomatorServiceProvider::class
        ];
    }

}
