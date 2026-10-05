<?php

namespace Simtabi\Laranail\FluentHttpAjaxify\Tests\Unit;

use Illuminate\Support\Facades\View;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Blade;
use Simtabi\Laranail\FluentHttpAjaxify\Tests\TestCase;

/**
 * An application override published before the view namespace gained its
 * `laranail/fluent-http-ajaxify` form (into views/vendor/laranail-fluent-http-ajaxify)
 * must keep winning, under both namespaces and through the Blade tag.
 */
class ViewOverrideTest extends TestCase
{
    private static string $views;

    protected function setUp(): void
    {
        self::$views = sys_get_temp_dir() . '/fha-views-' . bin2hex(random_bytes(4));
        $dir = self::$views . '/vendor/laranail-fluent-http-ajaxify/components';
        (new Filesystem)->ensureDirectoryExists($dir);
        file_put_contents($dir . '/scripts.blade.php', 'LEGACY-OVERRIDE');

        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        (new Filesystem)->deleteDirectory(self::$views);
    }

    public function test_a_legacy_published_override_still_wins(): void
    {
        $this->assertSame('LEGACY-OVERRIDE', trim(View::make('laranail/fluent-http-ajaxify::components.scripts')->render()));
        $this->assertSame('LEGACY-OVERRIDE', trim(View::make('laranail-fluent-http-ajaxify::components.scripts')->render()));
        $this->assertSame('LEGACY-OVERRIDE', trim(Blade::render('<x-laranail-fluent-http-ajaxify::scripts />')));
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('view.paths', [self::$views]);
    }
}
