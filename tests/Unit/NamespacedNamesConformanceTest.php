<?php

namespace Simtabi\Laranail\FluentHttpAjaxify\Tests\Unit;

use ArrayIterator;
use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\FluentHttpAjaxify\Commands\Concerns\SupportsNamespacedNames as NamespacedNames;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Throwable;

/**
 * Conformance for the `laranail::<slug>.<command>` naming trait.
 *
 * This package carries a local copy of `laranail/console`'s canonical trait. The
 * assertions are the same ones every carrier runs, so a copy that drifts fails
 * somewhere instead of shipping.
 *
 * @trait-copy-reason no-laranail-requirement This package's `require` holds no
 *   `laranail/*` entry; taking `laranail/console` on for one trait would add it and
 *   its VCS repositories to every consumer. Add any `laranail/*` requirement and
 *   `scripts/verify-trait-copies.py` in `laranail/package-tools` fails until this
 *   copy goes.
 */
class NamespacedNamesConformanceTest extends TestCase
{
    private function command(): SymfonyCommand
    {
        return new class extends SymfonyCommand
        {
            use NamespacedNames;
        };
    }

    public function test_it_accepts_a_name_symfony_validate_name_would_reject(): void
    {
        $this->assertSame('laranail::atlas.doctor', $this->command()->setName('laranail::atlas.doctor')->getName());
    }

    public function test_it_accepts_namespaced_aliases(): void
    {
        $this->assertSame(['laranail::atlas.dr'], $this->command()->setAliases(['laranail::atlas.dr'])->getAliases());
    }

    public function test_it_takes_aliases_from_any_iterable(): void
    {
        $this->assertSame(['laranail::atlas.x'], $this->command()->setAliases(new ArrayIterator(['laranail::atlas.x']))->getAliases());
    }

    public function test_it_returns_itself_so_the_calls_chain(): void
    {
        $command = $this->command();

        $this->assertSame($command, $command->setName('laranail::atlas.a'));
        $this->assertSame($command, $command->setAliases([]));
    }

    public function test_it_does_not_fatal_when_the_command_declares_no_alias_list(): void
    {
        try {
            $name = (string) $this->command()->setName('laranail::atlas.b')->getName();
        } catch (Throwable $e) {
            $this->fail('setName() threw without a $commandAliases declaration: ' . $e->getMessage());
        }

        $this->assertSame('laranail::atlas.b', $name);
    }

    public function test_an_empty_name_stays_empty(): void
    {
        $this->assertSame('', $this->command()->setName('')->getName());
    }

    public function test_a_command_can_declare_its_own_alias_list(): void
    {
        $command = new class extends SymfonyCommand
        {
            use NamespacedNames;

            /** @var list<string> */
            protected array $commandAliases = ['laranail::atlas.dr'];
        };

        $this->assertSame(['laranail::atlas.dr'], $command->setName('laranail::atlas.doctor')->getAliases());
    }
}
