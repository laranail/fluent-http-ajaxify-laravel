<?php

namespace Simtabi\Laranail\FluentHttpAjaxify\Commands;

use Illuminate\Console\Command;

/**
 * Full setup wizard for FluentHttpAjaxify.
 * Publishes config, JS assets, and optionally registers middleware.
 */
class InstallCommand extends Command
{
    protected $signature = 'ajaxify:install
                            {--force : Overwrite existing files}
                            {--no-assets : Skip JS asset publishing}';

    protected $description = 'Install FluentHttpAjaxify: publish config, assets, and set up middleware';

    public function handle(): int
    {
        $this->info('Installing FluentHttpAjaxify...');

        // Publish config
        $this->call('vendor:publish', [
            '--tag'   => 'laranail::fluent-http-ajaxify-config',
            '--force' => $this->option('force'),
        ]);
        $this->info('✓ Config published');

        // Publish JS assets
        if (!$this->option('no-assets')) {
            $this->call('vendor:publish', [
                '--tag'   => 'laranail::fluent-http-ajaxify-assets',
                '--force' => $this->option('force'),
            ]);
            $this->info('✓ JS assets published');
        }

        // Publish Blade components
        $this->call('vendor:publish', [
            '--tag'   => 'laranail::fluent-http-ajaxify-views',
            '--force' => $this->option('force'),
        ]);
        $this->info('✓ Blade components published');

        $this->newLine();
        $this->info('FluentHttpAjaxify installed successfully!');
        $this->newLine();

        $this->line('Next steps:');
        $this->line('  1. Add <x-laranail-fluent-http-ajaxify::scripts /> to your Blade layout (before </body>)');
        $this->line('  2. Register middleware in your kernel if needed:');
        $this->line("     'ajaxify.ajax' => \\Simtabi\\Laranail\\FluentHttpAjaxify\\Http\\Middleware\\FluentHttpAjaxifyMiddleware::class");
        $this->line("     'ajaxify.csrf' => \\Simtabi\\Laranail\\FluentHttpAjaxify\\Http\\Middleware\\InjectCsrfMeta::class");
        $this->line('  3. Use the HasFluentHttpAjaxify trait in your controllers');

        return self::SUCCESS;
    }
}
