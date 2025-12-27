<?php

namespace Companue\SharedUtilities\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            'Companue\\SharedUtilities\\Providers\\SharedUtilitiesServiceProvider',
        ];
    }
}
