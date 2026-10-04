<?php

namespace Simtabi\Laranail\FluentHttpAjaxify\Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Blade;
use InvalidArgumentException;
use Simtabi\Laranail\FluentHttpAjaxify\Tests\TestCase;

/**
 * Pins the names the README and docs/ tell a reader to type, against the
 * booted application, so a renamed view or command fails here instead of
 * in a consumer's layout.
 */
class DocumentedSurfaceTest extends TestCase
{
    /** The tag documented in README.md, docs/getting-started.md and docs/tools/usage.md. */
    public function test_the_documented_blade_tag_renders_the_scripts_component(): void
    {
        $html = Blade::render('<x-laranail-fluent-http-ajaxify::scripts :include-csrf="false" />');

        $this->assertStringContainsString('FluentHttpAjaxify.js', $html);
        $this->assertStringContainsString('axios.min.js', $html);
    }

    /** The view directory is not part of the tag: Blade adds `components.` itself. */
    public function test_the_tag_with_the_components_segment_does_not_resolve(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Blade::render('<x-laranail-fluent-http-ajaxify::components.scripts />');
    }

    /** The commands documented in docs/tools/commands.md, under the names the code registers. */
    public function test_the_documented_artisan_commands_are_registered(): void
    {
        $commands = array_keys(Artisan::all());

        foreach (['ajaxify:install', 'ajaxify:publish', 'ajaxify:update-axios'] as $name) {
            $this->assertContains($name, $commands);
        }
    }

    /** The publish tags documented in docs/installation.md and docs/tools/commands.md. */
    public function test_the_documented_publish_tags_exist(): void
    {
        $groups = array_keys(\Illuminate\Support\ServiceProvider::$publishGroups);

        foreach (['config', 'assets', 'views'] as $suffix) {
            $this->assertContains('laranail::fluent-http-ajaxify-' . $suffix, $groups);
        }
    }
}
