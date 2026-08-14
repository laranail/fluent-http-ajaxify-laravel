<?php

namespace Simtabi\Laranail\FluentHttpAjaxify\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Simtabi\Laranail\FluentHttpAjaxify\Providers\FluentHttpAjaxifyServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            FluentHttpAjaxifyServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Ajaxify' => \Simtabi\Laranail\FluentHttpAjaxify\Facade\Ajaxify::class,
        ];
    }
}
