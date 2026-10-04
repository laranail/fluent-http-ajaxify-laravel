<?php

namespace Simtabi\Laranail\FluentHttpAjaxify\Commands;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Fetch the latest (or a specific) Axios version from npm, download the
 * minified build, compute the SRI hash, and write both into the APPLICATION's
 * published copies: the local fallback in public/<assets_path>/axios.min.js and
 * the version + SRI in config/laranail/fluent-http-ajaxify.php.
 *
 * It never writes inside the package (vendor/): a file there is overwritten by
 * the next `composer update`, and the package's own config is not what the
 * application reads once it has published one. If either published copy is
 * missing, it says what to publish and exits non-zero.
 *
 * Usage:
 *   php artisan laranail::fluent-http-ajaxify.update-axios                        # upgrade to latest
 *   php artisan laranail::fluent-http-ajaxify.update-axios --axios-version=1.7.9  # pin a specific version
 *   php artisan laranail::fluent-http-ajaxify.update-axios --publish              # (re-)publish the assets first
 *   php artisan laranail::fluent-http-ajaxify.update-axios --dry-run              # show what would change
 */
class UpdateAxiosCommand extends Command
{
    /**
     * `--axios-version` replaces `--version`, which Artisan reserves globally:
     * declaring it made `--help` throw and `--version` print the framework version.
     */
    protected $signature = 'laranail::fluent-http-ajaxify.update-axios
                            {--axios-version= : Pin a specific Axios version instead of latest}
                            {--publish : (Re-)publish the JS assets to public/ before writing the new Axios build}
                            {--dry-run : Show what would change without writing anything}';

    /**
     * @deprecated `ajaxify:update-axios` goes in the next minor after 0.1; use `laranail::fluent-http-ajaxify.update-axios`.
     *
     * @var list<string>
     */
    protected array $commandAliases = ['ajaxify:update-axios'];

    protected $description = 'Download the latest Axios release, regenerate the SRI hash, and update the published config and assets';

    private const NPM_REGISTRY = 'https://registry.npmjs.org/axios/latest';
    private const CDN_TEMPLATE = 'https://cdn.jsdelivr.net/npm/axios@%s/dist/axios.min.js';

    public function handle(): int
    {
        $dryRun  = $this->option('dry-run');
        $current = config('laranail.fluent-http-ajaxify.axios_version', 'unknown');

        // ── 0. Require the published copies this command writes to ───────
        $configPath = $this->publishedConfigPath();
        $assetsDir  = $this->publishedAssetsDir();

        $missing = [];

        if (! is_file($configPath)) {
            $missing['config'] = $configPath;
        }

        if (! is_file($assetsDir . '/FluentHttpAjaxify.js') && ! $this->option('publish')) {
            $missing['assets'] = $assetsDir;
        }

        if ($missing !== []) {
            $report = $dryRun ? 'warn' : 'error';
            $this->{$report}('Nothing to update: this command only writes the application\'s published copies, and these are not published:');

            foreach ($missing as $tag => $path) {
                $this->line("  {$path}  (php artisan vendor:publish --tag=laranail::fluent-http-ajaxify-{$tag})");
            }

            $this->line('Publish them first (or run <comment>php artisan laranail::fluent-http-ajaxify.install</comment>), then re-run this command.');

            if (! $dryRun) {
                return self::FAILURE;
            }
        }

        // ── 1. Resolve target version ────────────────────────────────────
        $target = $this->option('axios-version');

        if (! $target) {
            $this->info('Checking npm registry for latest Axios version…');

            try {
                $response = Http::timeout(15)->get(self::NPM_REGISTRY);

                if (! $response->ok()) {
                    $this->error('Failed to reach npm registry (HTTP ' . $response->status() . ').');
                    return self::FAILURE;
                }

                $target = $response->json('version');
            } catch (\Throwable $e) {
                $this->error('Could not reach npm registry: ' . $e->getMessage());
                return self::FAILURE;
            }
        }

        if (! $target || ! preg_match('/^\d+\.\d+\.\d+/', $target)) {
            $this->error("Invalid version resolved: {$target}");
            return self::FAILURE;
        }

        $this->info("Current version : {$current}");
        $this->info("Target version  : {$target}");

        if ($current === $target) {
            $this->info('Already up to date.');
            return self::SUCCESS;
        }

        // ── 2. Download the minified build ───────────────────────────────
        $cdnUrl = sprintf(self::CDN_TEMPLATE, $target);
        $this->info("Downloading {$cdnUrl}…");

        try {
            $js = Http::timeout(30)->get($cdnUrl);

            if (! $js->ok()) {
                $this->error("CDN returned HTTP {$js->status()} — version {$target} may not exist.");
                return self::FAILURE;
            }
        } catch (\Throwable $e) {
            $this->error('Download failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        $body = $js->body();

        if (strlen($body) < 1000) {
            $this->error('Downloaded file is suspiciously small (' . strlen($body) . ' bytes). Aborting.');
            return self::FAILURE;
        }

        // Verify it looks like axios
        if (! Str::contains($body, 'axios', true)) {
            $this->error('Downloaded file does not appear to be Axios. Aborting.');
            return self::FAILURE;
        }

        $this->info('Downloaded ' . number_format(strlen($body)) . ' bytes.');

        // ── 3. Compute SRI hash ──────────────────────────────────────────
        $hash = 'sha384-' . base64_encode(hash('sha384', $body, true));
        $this->info("SRI hash: {$hash}");

        if ($dryRun) {
            $this->warn('Dry run — no files modified.');
            $this->table(
                ['Setting', 'Current', 'New'],
                [
                    ['axios_version', $current, $target],
                    ['axios_sri', Str::limit(config('laranail.fluent-http-ajaxify.axios_sri', ''), 40), Str::limit($hash, 40)],
                ],
            );
            return self::SUCCESS;
        }

        // ── 4. Optionally (re-)publish the package assets first ──────────
        // Before the write, not after: publishing with --force afterwards would
        // overwrite the new build with the package's bundled one.
        if ($this->option('publish')) {
            $this->call('vendor:publish', [
                '--tag'   => 'laranail::fluent-http-ajaxify-assets',
                '--force' => true,
            ]);
            $this->info('✓ Assets published to ' . $assetsDir);
        }

        // ── 5. Write the published local fallback file ───────────────────
        $localPath = $assetsDir . '/axios.min.js';
        file_put_contents($localPath, $body);
        $this->info("✓ Saved local copy → {$localPath}");

        // ── 6. Update the published config file ──────────────────────────
        $this->updateConfigFile($configPath, $target, $hash);
        $this->info("✓ Config updated → {$configPath}");

        $this->newLine();
        $this->info("Axios updated: {$current} → {$target}");

        return self::SUCCESS;
    }

    /**
     * The application's published config — the only config file this command
     * edits. The package's own copy under vendor/ is never touched.
     */
    private function publishedConfigPath(): string
    {
        return config_path('laranail/fluent-http-ajaxify.php');
    }

    /**
     * Where the assets publish tag puts the JS (public/<assets_path>).
     */
    private function publishedAssetsDir(): string
    {
        return public_path(trim((string) config('laranail.fluent-http-ajaxify.assets_path', 'vendor/fluent-http'), '/'));
    }

    /**
     * Perform safe string replacement in the config file for the version
     * and SRI values.
     */
    private function updateConfigFile(string $path, string $version, string $sri): void
    {
        $contents = file_get_contents($path);

        // Replace axios_version value
        $contents = preg_replace(
            "/('axios_version'\s*=>\s*')[^']+(')/",
            "\${1}{$version}\${2}",
            $contents,
        );

        // Replace axios_sri value
        $contents = preg_replace(
            "/('axios_sri'\s*=>\s*')[^']+(')/",
            "\${1}{$sri}\${2}",
            $contents,
        );

        file_put_contents($path, $contents);
    }
}
