<?php

namespace Simtabi\Laranail\FluentHttpAjaxify\Tests\Unit;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Simtabi\Laranail\FluentHttpAjaxify\Tests\TestCase;

/**
 * Every script the Blade component asks the browser for must be a file the
 * package actually ships, because the assets tag publishes resources/js
 * verbatim into public/<assets_path>. A reference to anything else is a 404
 * on every page that uses the tag.
 *
 * The rendered HTML is read rather than the template source, so a guarded
 * reference (one wrapped in file_exists()) only counts when it is emitted.
 */
class ScriptAssetsTest extends TestCase
{
    private ?string $publicPath = null;

    protected function tearDown(): void
    {
        if ($this->publicPath !== null) {
            File::deleteDirectory($this->publicPath);
        }

        parent::tearDown();
    }

    public function test_every_unconditional_asset_the_view_requests_is_shipped(): void
    {
        $requested = $this->requestedAssets(
            Blade::render('<x-laranail-fluent-http-ajaxify::scripts :include-csrf="false" />'),
        );

        // Non-vacuity: the axios fallback and the client itself, at minimum.
        $this->assertGreaterThanOrEqual(2, count($requested), 'The view emitted fewer asset references than expected; the extraction is broken.');

        $shipped = dirname(__DIR__, 2) . '/resources/js/';

        foreach ($requested as $file) {
            $this->assertFileExists($shipped . $file, "The scripts view requests {$file}, which the package does not ship.");
        }
    }

    public function test_optional_scripts_are_only_requested_once_published(): void
    {
        $this->publicPath = sys_get_temp_dir() . '/fha-public-' . bin2hex(random_bytes(4));
        $this->app->usePublicPath($this->publicPath);

        $assets = $this->publicPath . '/' . config('laranail.fluent-http-ajaxify.assets_path');
        File::ensureDirectoryExists($assets);
        File::put($assets . '/FluentToast.js', '/* published from the npm package */');

        $requested = $this->requestedAssets(
            Blade::render('<x-laranail-fluent-http-ajaxify::scripts :include-csrf="false" />'),
        );

        $this->assertContains('FluentToast.js', $requested);
        $this->assertNotContains('FluentHttpWrapper.js', $requested);
    }

    /**
     * @return list<string> basenames of every URL under the configured assets path
     */
    private function requestedAssets(string $html): array
    {
        $prefix = preg_quote(trim((string) config('laranail.fluent-http-ajaxify.assets_path'), '/'), '#');

        preg_match_all('#/' . $prefix . '/([A-Za-z0-9._-]+\.js)#', $html, $matches);

        return array_values(array_unique($matches[1]));
    }
}
