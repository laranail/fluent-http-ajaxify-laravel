<?php

namespace Simtabi\Laranail\FluentHttpAjaxify\Commands;

use Illuminate\Console\Command;

/**
 * Publish JS/CSS assets to the public directory.
 */
class PublishCommand extends Command
{
    protected $signature = 'ajaxify:publish
                            {--force : Overwrite existing files}';

    protected $description = 'Publish FluentHttpAjaxify JS assets to public directory';

    public function handle(): int
    {
        $this->call('vendor:publish', [
            '--tag'   => 'laranail::fluent-http-ajaxify-assets',
            '--force' => $this->option('force'),
        ]);

        $this->info('FluentHttpAjaxify assets published successfully.');

        return self::SUCCESS;
    }
}
