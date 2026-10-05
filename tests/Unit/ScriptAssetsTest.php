<?php

namespace Simtabi\Laranail\FluentHttpAjaxify\Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Blade;
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

    public function test_every_asset_the_view_can_reference_is_shipped(): void
    {
        // Read the template, not the render: the guarded references (FluentToast,
        // FluentHttpWrapper) are only emitted once published, and publishing copies
        // resources/js -- so each of them has to exist there for the guard to ever pass.
        $template = (string) file_get_contents(dirname(__DIR__, 2) . '/resources/views/components/scripts.blade.php');

        preg_match_all('#\$assetsPath\s*\.\s*\x27/([A-Za-z0-9._-]+)\x27#', $template, $matches);
        $referenced = array_values(array_unique($matches[1]));

        // Non-vacuity: axios fallback, the client, FluentToast and FluentHttpWrapper.
        $this->assertGreaterThanOrEqual(4, count($referenced), 'The template scan found fewer asset references than the view makes; the pattern is broken.');

        foreach ($referenced as $file) {
            $this->assertFileExists(self::shippedPath($file), "The scripts view can request {$file}, which resources/js does not ship.");
        }
    }

    public function test_the_shipped_client_is_the_real_build_not_the_placeholder(): void
    {
        $client = self::shippedPath('FluentHttpAjaxify.js');

        // The placeholder was 232 bytes; the v3 client is ~184 KB.
        $this->assertGreaterThan(100_000, (int) filesize($client), 'resources/js/FluentHttpAjaxify.js is too small to be the client; is it the placeholder again?');

        $source = (string) file_get_contents($client);

        $this->assertStringNotContainsString('This is a placeholder', $source);
        $this->assertStringContainsString('root.FluentHttpAjaxify = factory()', $source, 'The UMD export of FluentHttpAjaxify is missing.');
        $this->assertStringContainsString('static getRegistration()', $source);
        // Only the first line: matching against the whole file would dump 180 KB on failure.
        $header = strtok($source, "\n");
        $this->assertMatchesRegularExpression('#^// Synced from laranail/fluent-http-ajaxify-js@[0-9a-f]{7,40} #', (string) $header, 'The client carries no source-commit header; run bin/sync-client against a committed checkout.');
    }

    public function test_the_shipped_toast_module_is_the_real_build(): void
    {
        $toast = self::shippedPath('FluentToast.js');

        $this->assertFileExists($toast);
        $this->assertGreaterThan(10_000, (int) filesize($toast), 'resources/js/FluentToast.js is too small to be the toast module.');
        $this->assertStringContainsString('root.FluentToast = factory()', (string) file_get_contents($toast), 'The UMD export of FluentToast is missing.');
    }

    private static function shippedPath(string $file): string
    {
        return dirname(__DIR__, 2) . '/resources/js/' . $file;
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
