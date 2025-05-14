<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\HttpKernel\KernelInterface;

class ClubsAndFixtures extends Fixture
{
    private KernelInterface $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        $this->runCommand([
            'file' => 'assets/clubs.csv',
            '--type' => 'club',
            '--skip-first' => true,
        ]);

        $this->runCommand([
            'file' => 'assets/fixtures.csv',
            '--skip-first' => true,
        ]);
    }

    private function runCommand(array $arguments): void
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(array_merge(
            ['command' => 'nrfc:fixtures:import'],
            $arguments
        ));

        $output = new BufferedOutput();
        $exitCode = $application->run($input, $output);

        if (0 !== $exitCode) {
            throw new RuntimeException(sprintf('Command "nrfc:fixtures:import" failed with code %d. Output: %s', $exitCode, $output->fetch()));
        }

        (new ConsoleOutput())->writeln($output->fetch());
    }
}
