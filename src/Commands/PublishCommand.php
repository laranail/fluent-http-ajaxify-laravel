<?php

namespace Simtabi\Laranail\FluentHttpAjaxify\Commands;

/**
 * Publish JS/CSS assets to the public directory.
 */
class PublishCommand extends Command
{
    protected $signature = 'laranail::fluent-http-ajaxify.publish
                            {--force : Overwrite existing files}';

    /**
     * @deprecated `ajaxify:publish` goes in the next minor after 0.1; use `laranail::fluent-http-ajaxify.publish`.
     *
     * @var list<string>
     */
    protected array $commandAliases = ['ajaxify:publish'];

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
