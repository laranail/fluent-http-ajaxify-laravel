<?php

declare(strict_types=1);

namespace Simtabi\Laranail\FluentHttpAjaxify\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Illuminate\Console\Command as IlluminateCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Simtabi\Laranail\FluentHttpAjaxify\Commands\Concerns\SupportsNamespacedNames;

/**
 * Base for this package's commands: the `laranail::fluent-http-ajaxify.<command>`
 * name shape, plus a warning when a command is invoked by one of its deprecated
 * bare aliases.
 */
abstract class Command extends IlluminateCommand
{
    use SupportsNamespacedNames;

    /**
     * Deprecated bare names this command still answers to.
     *
     * @deprecated The bare `ajaxify:*` aliases go in the next minor after 0.1.
     *
     * @var list<string>
     */
    protected array $commandAliases = [];

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $invokedAs = $input->getFirstArgument();

        if (is_string($invokedAs) && in_array($invokedAs, $this->commandAliases, true)) {
            $this->warn(sprintf(
                '`%s` is deprecated and will be removed in the next minor after 0.1. Use `%s` instead.',
                $invokedAs,
                $this->getName(),
            ));
        }

        return parent::execute($input, $output);
    }
}
