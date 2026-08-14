<?php

namespace Simtabi\Laranail\FluentHttpAjaxify\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Fetch the latest (or a specific) Axios version from npm, download the
 * minified build, compute the SRI hash, update the config file, and
 * optionally re-publish assets to the public directory.
 *
 * Usage:
 *   php artisan ajaxify:update-axios              # upgrade to latest
 *   php artisan ajaxify:update-axios --version=1.7.9  # pin a specific version
 *   php artisan ajaxify:update-axios --publish     # also re-publish to public/
 *   php artisan ajaxify:update-axios --dry-run     # show what would change
 */
class UpdateAxiosCommand extends Command
{
    protected $signature = 'ajaxify:update-axios
                            {--version= : Pin a specific Axios version instead of latest}
                            {--publish : Re-publish JS assets to public/ after updating}
                            {--dry-run : Show what would change without writing anything}';

    protected $description = 'Download the latest Axios release, regenerate the SRI hash, and update the config';

    private const NPM_REGISTRY = 'https://registry.npmjs.org/axios/latest';
    private const CDN_TEMPLATE = 'https://cdn.jsdelivr.net/npm/axios@%s/dist/axios.min.js';

    public function handle(): int
    {
        $dryRun  = $this->option('dry-run');
        $current = config('laranail.fluent-http-ajaxify.axios_version', 'unknown');

        // ── 1. Resolve target version ────────────────────────────────────
        $target = $this->option('version');

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

        // ── 4. Write the local fallback file ─────────────────────────────
        $localPath = dirname(__DIR__, 2) . '/resources/js/axios.min.js';
        file_put_contents($localPath, $body);
        $this->info("✓ Saved local copy → {$localPath}");

        // ── 5. Update the config file ────────────────────────────────────
        $configPath = $this->resolveConfigPath();

        if (! $configPath) {
            $this->warn('Could not locate config file to auto-update. Please update manually:');
            $this->line("  'axios_version' => '{$target}',");
            $this->line("  'axios_sri'     => '{$hash}',");
        } else {
            $this->updateConfigFile($configPath, $target, $hash);
            $this->info("✓ Config updated → {$configPath}");
        }

        // ── 6. Optionally re-publish assets ──────────────────────────────
        if ($this->option('publish')) {
            $this->call('vendor:publish', [
                '--tag'   => 'ajaxify-assets',
                '--force' => true,
            ]);
            $this->info('✓ Assets re-published to public/');
        }

        $this->newLine();
        $this->info("Axios updated: {$current} → {$target}");

        if (! $this->option('publish')) {
            $this->line('Run <comment>php artisan ajaxify:publish --force</comment> to push the new file to public/.');
        }

        return self::SUCCESS;
    }

    /**
     * Locate the config file — prefer the published app copy, fall back to
     * the package's own copy.
     */
    private function resolveConfigPath(): ?string
    {
        $appConfig = config_path('laranail/fluent-http-ajaxify.php');

        if (file_exists($appConfig)) {
            return $appConfig;
        }

        $packageConfig = dirname(__DIR__, 2) . '/config/fluent-http-ajaxify.php';

        return file_exists($packageConfig) ? $packageConfig : null;
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
