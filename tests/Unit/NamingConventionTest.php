<?php

namespace Simtabi\Laranail\FluentHttpAjaxify\Tests\Unit;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use ReflectionClass;
use Simtabi\Laranail\FluentHttpAjaxify\Tests\TestCase;

/**
 * The org naming guard, read from the LIVE registries — flat maps
 * collide silently, so no bare slug may appear.
 */
class NamingConventionTest extends TestCase
{
    public function test_views_register_only_under_the_org_namespace(): void
    {
        $hints = View::getFinder()->getHints();

        $this->assertArrayHasKey('laranail-fluent-http-ajaxify', $hints);
        $this->assertArrayNotHasKey('fluent-http-ajaxify', $hints);
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
