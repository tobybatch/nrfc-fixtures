<?php

namespace App\Tests\Unit\Command;

use App\Command\NrfcFixturesImportCommand;
use App\Repository\ClubRepository;
use App\Service\ImportExportService;
use App\Service\TeamService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\BrowserKit\Exception\BadMethodCallException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpKernel\KernelInterface;

class NrfcFixturesImportCommandTest extends TestCase
{
    private ImportExportService $importOutputService;
    private KernelInterface $kernel;
    private ParameterBagInterface $parameterBag;
    private string $tempFixtureFile = __DIR__.'/../../../assets/fixtures-youth-2025-6.csv';
    private string $tempClubFile = __DIR__.'/../../../assets/clubs.csv';
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->importOutputService = $this->createMock(ImportExportService::class);
        $this->kernel = $this->createMock(KernelInterface::class);
        $this->parameterBag = $this->createMock(ParameterBagInterface::class);

        $application = new Application();
        $application->add(new NrfcFixturesImportCommand($this->importOutputService, $this->kernel, $this->parameterBag));

        $command = $application->find('nrfc:fixtures:import');
        $this->commandTester = new CommandTester($command);
    }

    public function testExecuteWithNonExistentFile(): void
    {
        $this->expectException(FileNotFoundException::class);
        $this->commandTester->execute([
            'file' => 'non_existent_file.csv',
        ]);
    }

    public function testCommandDescription(): void
    {
        $application = new Application();
        $command = new NrfcFixturesImportCommand($this->importOutputService, $this->kernel, $this->parameterBag);
        $application->add($command);

        $this->assertEquals('Import fixture data from CSV file and create entities', $command->getDescription());
        $this->assertNotEmpty($command->getHelp());

        $definition = $command->getDefinition();
        $this->assertTrue($definition->hasArgument('file'));
    }

    public function testCommandHelp(): void
    {
        $this->expectException(FileNotFoundException::class);
        $this->commandTester->execute(
            ['file' => 'non_existent_file.csv', '--help' => true],
        );
        $this->assertEquals(Command::SUCCESS, $this->commandTester->getStatusCode());
    }

    public function testErrorInTopLevel(): void
    {
        // Doesn't really test anything, it's for coverage
        $this->commandTester->execute([
            'file' => $this->tempClubFile
        ]);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Import failed', trim($output));
        $this->assertEquals(Command::FAILURE, $this->commandTester->getStatusCode());
    }
}
