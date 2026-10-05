<?php

namespace Simtabi\Laranail\FluentHttpAjaxify\Tests\Unit;

use Illuminate\Support\Facades\Http;
use Illuminate\Filesystem\Filesystem;
use Simtabi\Laranail\FluentHttpAjaxify\Tests\TestCase;

/**
 * `update-axios` writes the application's published copies and nothing else:
 * public/<assets_path>/axios.min.js and config/laranail/fluent-http-ajaxify.php.
 * Run against a throwaway base path so a write into the package (vendor/) or
 * the real testbench skeleton would show up as a changed file.
 */
class UpdateAxiosCommandTest extends TestCase
{
    private static string $root;

    private string $packageAxiosHash;

    private string $packageConfigHash;

    protected function setUp(): void
    {
        self::$root = sys_get_temp_dir() . '/fha-base-' . bin2hex(random_bytes(4));
        (new Filesystem)->ensureDirectoryExists(self::$root . '/public');
        (new Filesystem)->ensureDirectoryExists(self::$root . '/config');

        $this->packageAxiosHash = hash_file('sha256', $this->packagePath('resources/js/axios.min.js'));
        $this->packageConfigHash = hash_file('sha256', $this->packagePath('config/fluent-http-ajaxify.php'));

        parent::setUp();

        Http::fake([
            'registry.npmjs.org/*' => Http::response(['version' => '1.99.0']),
            'cdn.jsdelivr.net/*'   => Http::response('/* axios v1.99.0 */' . str_repeat(' ', 2000)),
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        (new Filesystem)->deleteDirectory(self::$root);

        // Whatever happened, the package itself must be untouched.
        $this->assertSame($this->packageAxiosHash, hash_file('sha256', $this->packagePath('resources/js/axios.min.js')));
        $this->assertSame($this->packageConfigHash, hash_file('sha256', $this->packagePath('config/fluent-http-ajaxify.php')));
    }

    public function test_it_refuses_and_exits_non_zero_when_nothing_is_published(): void
    {
        $this->artisan('laranail::fluent-http-ajaxify.update-axios', ['--axios-version' => '1.99.0'])
            ->expectsOutputToContain('not published')
            ->expectsOutputToContain('--tag=laranail::fluent-http-ajaxify-config')
            ->expectsOutputToContain('--tag=laranail::fluent-http-ajaxify-assets')
            ->assertFailed();

        $this->assertFileDoesNotExist(self::$root . '/public/vendor/fluent-http/axios.min.js');
        Http::assertNothingSent();
    }

    public function test_it_refuses_when_only_the_assets_are_published(): void
    {
        $this->artisan('vendor:publish', ['--tag' => 'laranail::fluent-http-ajaxify-assets'])->assertSuccessful();

        $this->artisan('laranail::fluent-http-ajaxify.update-axios', ['--axios-version' => '1.99.0'])
            ->expectsOutputToContain('--tag=laranail::fluent-http-ajaxify-config')
            ->assertFailed();
    }

    public function test_it_writes_the_published_asset_and_the_published_config(): void
    {
        $this->publishAll();

        $this->artisan('laranail::fluent-http-ajaxify.update-axios', ['--axios-version' => '1.99.0'])
            ->assertSuccessful();

        $axios = self::$root . '/public/vendor/fluent-http/axios.min.js';
        $this->assertStringContainsString('axios v1.99.0', (string) file_get_contents($axios));

        $config = (string) file_get_contents(self::$root . '/config/laranail/fluent-http-ajaxify.php');
        $this->assertStringContainsString("'axios_version' => '1.99.0'", $config);

        $expectedSri = 'sha384-' . base64_encode(hash('sha384', (string) file_get_contents($axios), true));
        $this->assertStringContainsString("'axios_sri' => '{$expectedSri}'", $config);
    }

    public function test_the_axios_version_option_pins_the_version_without_asking_npm(): void
    {
        $this->publishAll();

        $this->artisan('laranail::fluent-http-ajaxify.update-axios', ['--axios-version' => '1.50.0'])
            ->assertSuccessful();

        Http::assertNotSent(fn ($request): bool => str_contains($request->url(), 'registry.npmjs.org'));
        Http::assertSent(fn ($request): bool => str_contains($request->url(), 'axios@1.50.0'));
    }

    public function test_publish_option_publishes_the_assets_before_writing(): void
    {
        $this->artisan('vendor:publish', ['--tag' => 'laranail::fluent-http-ajaxify-config'])->assertSuccessful();

        $this->artisan('laranail::fluent-http-ajaxify.update-axios', ['--axios-version' => '1.99.0', '--publish' => true])
            ->assertSuccessful();

        $this->assertFileExists(self::$root . '/public/vendor/fluent-http/FluentHttpAjaxify.js');
        $this->assertStringContainsString('axios v1.99.0', (string) file_get_contents(self::$root . '/public/vendor/fluent-http/axios.min.js'));
    }

    public function test_dry_run_writes_nothing(): void
    {
        $this->publishAll();
        $before = hash_file('sha256', self::$root . '/public/vendor/fluent-http/axios.min.js');

        $this->artisan('laranail::fluent-http-ajaxify.update-axios', ['--axios-version' => '1.99.0', '--dry-run' => true])
            ->assertSuccessful();

        $this->assertSame($before, hash_file('sha256', self::$root . '/public/vendor/fluent-http/axios.min.js'));
    }

    public function test_help_no_longer_collides_with_the_global_version_option(): void
    {
        $this->artisan('help', ['command_name' => 'laranail::fluent-http-ajaxify.update-axios'])
            ->expectsOutputToContain('--axios-version')
            ->assertSuccessful();
    }

    protected function defineEnvironment($app): void
    {
        // Before the provider boots, so the publish groups point here too.
        $app->usePublicPath(self::$root . '/public');
        $app->useConfigPath(self::$root . '/config');
    }

    private function publishAll(): void
    {
        $this->artisan('vendor:publish', ['--tag' => 'laranail::fluent-http-ajaxify-config'])->assertSuccessful();
        $this->artisan('vendor:publish', ['--tag' => 'laranail::fluent-http-ajaxify-assets'])->assertSuccessful();
    }

    private function packagePath(string $path): string
    {
        return dirname(__DIR__, 2) . '/' . $path;
    }
}
