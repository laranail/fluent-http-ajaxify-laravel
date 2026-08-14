<?php

namespace Simtabi\Laranail\FluentHttpAjaxify\Providers;

use Illuminate\Support\ServiceProvider;
use Simtabi\Laranail\FluentHttpAjaxify\Commands\InstallCommand;
use Simtabi\Laranail\FluentHttpAjaxify\Commands\PublishCommand;
use Simtabi\Laranail\FluentHttpAjaxify\Commands\UpdateAxiosCommand;
use Simtabi\Laranail\FluentHttpAjaxify\Contracts\FluentHttpAjaxifyInterface;
use Simtabi\Laranail\FluentHttpAjaxify\FluentHttpAjaxify;

class FluentHttpAjaxifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/fluent-http-ajaxify.php', 'laranail.fluent-http-ajaxify');

        $this->app->singleton(FluentHttpAjaxifyInterface::class, function ($app) {
            return new FluentHttpAjaxify($app['request']);
        });

        $this->app->alias(FluentHttpAjaxifyInterface::class, 'fluent-http-ajaxify');
    }

    public function boot(): void
    {
        // Config
        $this->publishes([
            __DIR__ . '/../../config/fluent-http-ajaxify.php' => config_path('laranail/fluent-http-ajaxify.php'),
        ], 'ajaxify-config');

        // JS assets
        $this->publishes([
            __DIR__ . '/../../resources/js' => public_path(config('laranail.fluent-http-ajaxify.assets_path', 'vendor/fluent-http')),
        ], 'ajaxify-assets');

        // Blade views
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'fluent-http-ajaxify');

        $this->publishes([
            __DIR__ . '/../../resources/views' => resource_path('views/vendor/fluent-http-ajaxify'),
        ], 'ajaxify-views');

        // Commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                PublishCommand::class,
                UpdateAxiosCommand::class,
            ]);
        }
    }
}
