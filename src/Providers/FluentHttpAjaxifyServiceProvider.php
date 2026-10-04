<?php

namespace Simtabi\Laranail\FluentHttpAjaxify\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Simtabi\Laranail\FluentHttpAjaxify\Commands\InstallCommand;
use Simtabi\Laranail\FluentHttpAjaxify\Commands\PublishCommand;
use Simtabi\Laranail\FluentHttpAjaxify\Commands\UpdateAxiosCommand;
use Simtabi\Laranail\FluentHttpAjaxify\Contracts\FluentHttpAjaxifyInterface;
use Simtabi\Laranail\FluentHttpAjaxify\FluentHttpAjaxify;
use Simtabi\Laranail\FluentHttpAjaxify\Http\Middleware\FluentHttpAjaxifyMiddleware;
use Simtabi\Laranail\FluentHttpAjaxify\Http\Middleware\InjectCsrfMeta;

class FluentHttpAjaxifyServiceProvider extends ServiceProvider
{
    /** View namespace: the composer package name, so `view()` names trace to the package. */
    public const VIEW_NAMESPACE = 'laranail/fluent-http-ajaxify';

    /** Blade component prefix: Blade's tag parser admits no slash, so tags keep the hyphen. */
    public const COMPONENT_PREFIX = 'laranail-fluent-http-ajaxify';

    /** Route middleware aliases. Hyphens only: Laravel splits a middleware name on `:`. */
    public const MIDDLEWARE_AJAX = 'laranail-fluent-http-ajaxify-ajax';

    public const MIDDLEWARE_CSRF = 'laranail-fluent-http-ajaxify-csrf';

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/fluent-http-ajaxify.php', 'laranail.fluent-http-ajaxify');

        $this->app->singleton(FluentHttpAjaxifyInterface::class, function ($app) {
            return new FluentHttpAjaxify($app['request']);
        });

        $this->app->alias(FluentHttpAjaxifyInterface::class, 'laranail-fluent-http-ajaxify');
    }

    public function boot(): void
    {
        // Config
        $this->publishes([
            __DIR__ . '/../../config/fluent-http-ajaxify.php' => config_path('laranail/fluent-http-ajaxify.php'),
        ], 'laranail::fluent-http-ajaxify-config');

        // JS assets
        $this->publishes([
            __DIR__ . '/../../resources/js' => public_path(config('laranail.fluent-http-ajaxify.assets_path', 'vendor/fluent-http')),
        ], 'laranail::fluent-http-ajaxify-assets');

        // Blade views
        $this->registerViews();

        $this->publishes([
            __DIR__ . '/../../resources/views' => resource_path('views/vendor/' . self::VIEW_NAMESPACE),
        ], 'laranail::fluent-http-ajaxify-views');

        // Middleware aliases
        $this->callAfterResolving('router', function (Router $router): void {
            $router->aliasMiddleware(self::MIDDLEWARE_AJAX, FluentHttpAjaxifyMiddleware::class);
            $router->aliasMiddleware(self::MIDDLEWARE_CSRF, InjectCsrfMeta::class);
        });

        // Commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                PublishCommand::class,
                UpdateAxiosCommand::class,
            ]);
        }
    }

    /**
     * Register the views under both names over the same resolved paths.
     *
     * `laranail/fluent-http-ajaxify::` is the view namespace; the hyphenated
     * `laranail-fluent-http-ajaxify::` is what Blade component tags resolve
     * through (and what views were registered under before), so both must find
     * the same files. Application overrides are read from the current publish
     * directory (`views/vendor/laranail/fluent-http-ajaxify`) and from the one
     * earlier releases published to (`views/vendor/laranail-fluent-http-ajaxify`),
     * so an override published before the rename keeps winning.
     */
    private function registerViews(): void
    {
        $packagePath = __DIR__ . '/../../resources/views';

        $this->callAfterResolving('view', function (ViewFactory $view) use ($packagePath): void {
            $paths = [];

            foreach ((array) $this->app['config']->get('view.paths', []) as $viewPath) {
                foreach ([self::VIEW_NAMESPACE, self::COMPONENT_PREFIX] as $directory) {
                    if (is_dir($override = $viewPath . '/vendor/' . $directory)) {
                        $paths[] = $override;
                    }
                }
            }

            $paths[] = $packagePath;

            foreach ([self::VIEW_NAMESPACE, self::COMPONENT_PREFIX] as $namespace) {
                $view->addNamespace($namespace, $paths);
            }
        });
    }
}
