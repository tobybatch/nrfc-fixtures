<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use JetBrains\PhpStorm\NoReturn;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\HttpKernel\KernelInterface;

class ClubsAndFixtures extends Fixture
{
    private KernelInterface $kernel;
    private LoggerInterface $logger;

    public function __construct(KernelInterface $kernel, LoggerInterface $logger)
    {
        $this->kernel = $kernel;
        $this->logger = $logger;
    }

    /**
     * @throws \Exception
     */
    #[NoReturn]
    public function load(ObjectManager $manager): void
    {
        $files = glob('assets/fixtures-*.csv');
        foreach ($files as $file) {
            $this->logger->info("Loading fixtures from " . ($file));
            $this->runCommand([
                'file' => $file,
            ]);
        }
    }

    /**
     * @param array<string, string|true> $arguments
     *
     * @throws \Exception
     */
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
            throw new \RuntimeException(sprintf('Command "nrfc:fixtures:import" failed with code %d. Output: %s', $exitCode, $output->fetch()));
        }

        (new ConsoleOutput())->writeln($output->fetch());
    }
}
