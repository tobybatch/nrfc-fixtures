<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\HttpKernel\KernelInterface;

class Fixtures extends Fixture
{
    private KernelInterface $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    public function getDependencies(): array
    {
        return [
            Fixtures::class,
        ];
    }

    /**
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        $commandName = "nrfc:fixtures:import";
        $arguments = [
            'file' => 'var/fixtures.csv',
            '--skip-first' => true,
        ];

        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(array_merge(
            ['command' => $commandName],
            $arguments
        ));

        $output = new BufferedOutput();
        $exitCode = $application->run($input, $output);

        if ($exitCode !== 0) {
            throw new \RuntimeException(
                sprintf('Command "%s" failed with code %d. Output: %s',
                    $commandName,
                    $exitCode,
                    $output->fetch()
                )
            );
        }

        (new ConsoleOutput())->writeln($output->fetch());

    }
}
