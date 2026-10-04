<?php

namespace Simtabi\Laranail\FluentHttpAjaxify\Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use ReflectionClass;
use Simtabi\Laranail\FluentHttpAjaxify\Http\Middleware\FluentHttpAjaxifyMiddleware;
use Simtabi\Laranail\FluentHttpAjaxify\Http\Middleware\InjectCsrfMeta;
use Simtabi\Laranail\FluentHttpAjaxify\Tests\TestCase;

/**
 * The org naming guard, read from the LIVE registries — flat maps
 * collide silently, so no bare slug may appear.
 */
class NamingConventionTest extends TestCase
{
    public function test_views_register_under_the_package_name_and_the_component_prefix(): void
    {
        $hints = View::getFinder()->getHints();

        // View namespace: the composer package name. Component prefix: the hyphen
        // form, because Blade's tag parser admits no slash. Both over the same paths.
        $this->assertArrayHasKey('laranail/fluent-http-ajaxify', $hints);
        $this->assertArrayHasKey('laranail-fluent-http-ajaxify', $hints);
        $this->assertSame($hints['laranail/fluent-http-ajaxify'], $hints['laranail-fluent-http-ajaxify']);

        $this->assertArrayNotHasKey('fluent-http-ajaxify', $hints);
        $this->assertArrayNotHasKey('ajaxify', $hints);
    }

    public function test_both_view_namespaces_resolve_the_scripts_view(): void
    {
        $this->assertTrue(View::exists('laranail/fluent-http-ajaxify::components.scripts'));
        $this->assertTrue(View::exists('laranail-fluent-http-ajaxify::components.scripts'));
    }

    public function test_blade_cannot_take_the_slash_form_as_a_component_prefix(): void
    {
        // Pins the reason the tag keeps the hyphen: Blade's own name pattern
        // truncates at the slash, so `<x-laranail/...::scripts />` never compiles.
        preg_match('/<\s*x[-\:]([\w\-\:\.]*)/x', '<x-laranail/fluent-http-ajaxify::scripts />', $m);
        $this->assertSame('laranail', $m[1]);

        $this->assertStringContainsString(
            'FluentHttpAjaxify.js',
            Blade::render('<x-laranail-fluent-http-ajaxify::scripts :include-csrf="false" />'),
        );
    }

    public function test_commands_are_vendor_scoped_with_the_bare_names_as_deprecated_aliases(): void
    {
        $commands = Artisan::all();

        foreach (['install', 'publish', 'update-axios'] as $name) {
            $scoped = 'laranail::fluent-http-ajaxify.' . $name;

            $this->assertArrayHasKey($scoped, $commands);
            $this->assertSame($scoped, $commands[$scoped]->getName());
            $this->assertSame(['ajaxify:' . $name], $commands[$scoped]->getAliases());
        }

        // Nothing else of ours, and in particular no other bare alias.
        $ours = array_filter(
            array_keys($commands),
            fn (string $n): bool => str_contains($n, 'ajaxify'),
        );
        sort($ours);

        $this->assertSame([
            'ajaxify:install',
            'ajaxify:publish',
            'ajaxify:update-axios',
            'laranail::fluent-http-ajaxify.install',
            'laranail::fluent-http-ajaxify.publish',
            'laranail::fluent-http-ajaxify.update-axios',
        ], $ours);
    }

    public function test_a_deprecated_command_alias_still_runs_and_warns(): void
    {
        $this->artisan('ajaxify:publish')
            ->expectsOutputToContain('`ajaxify:publish` is deprecated and will be removed in the next minor after 0.1. Use `laranail::fluent-http-ajaxify.publish` instead.')
            ->assertSuccessful();
    }

    public function test_the_scoped_command_name_does_not_warn(): void
    {
        $this->artisan('laranail::fluent-http-ajaxify.publish')
            ->doesntExpectOutputToContain('deprecated')
            ->assertSuccessful();
    }

    public function test_middleware_aliases_are_vendor_scoped(): void
    {
        $middleware = app('router')->getMiddleware();

        $this->assertSame(FluentHttpAjaxifyMiddleware::class, $middleware['laranail-fluent-http-ajaxify-ajax'] ?? null);
        $this->assertSame(InjectCsrfMeta::class, $middleware['laranail-fluent-http-ajaxify-csrf'] ?? null);

        foreach (array_keys($middleware) as $alias) {
            if (str_contains($alias, 'ajaxify')) {
                // Laravel splits a middleware name on ':' for parameters.
                $this->assertStringNotContainsString(':', $alias);
                $this->assertStringStartsWith('laranail-fluent-http-ajaxify-', $alias);
            }
        }
    }

    public function test_the_service_alias_is_vendor_scoped(): void
    {
        $this->assertTrue(app()->bound('laranail-fluent-http-ajaxify'));
        $this->assertFalse(app()->bound('fluent-http-ajaxify'));
    }

    public function test_every_publish_tag_is_namespaced(): void
    {
        $reflection = new ReflectionClass(ServiceProvider::class);
        $groups = array_keys($reflection->getProperty('publishGroups')->getValue());
        $ours = array_filter($groups, fn (int|string $t): bool => str_contains((string) $t, 'ajaxify'));

        $this->assertNotEmpty($ours);

        foreach ($ours as $tag) {
            $this->assertStringStartsWith('laranail::fluent-http-ajaxify', (string) $tag);
        }
    }
}
