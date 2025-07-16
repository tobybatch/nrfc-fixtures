<?php

namespace App\Tests\Unit\Command;

use App\Command\NrfcFixturesDebugCommand;
use App\Command\NrfcFixturesImportCommand;
use App\Repository\ClubRepository;
use App\Service\TeamService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\BrowserKit\Exception\BadMethodCallException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;


class NrfcFixturesImportCommandTest extends TestCase
{
    private CommandTester $commandTester;
    private EntityManagerInterface $entityManager;
    private ClubRepository $clubRepository;
    private TeamService $teamService;
    private string $tempFixtureFile = __DIR__ . '/../../../assets/fixtures-youth-2025-6.csv';
    private string $tempClubFile = __DIR__ . '/../../../assets/clubs.csv';

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->clubRepository = $this->createMock(ClubRepository::class);
        $this->teamService = $this->createMock(TeamService::class);

        $application = new Application();
        $application->add(new NrfcFixturesImportCommand($this->entityManager, $this->teamService, $this->clubRepository));
        
        $command = $application->find('nrfc:fixtures:import');
        $this->commandTester = new CommandTester($command);
    }

    public function testExecuteWithNonExistentFile(): void
    {
        $this->expectException(FileNotFoundException::class);
        $this->commandTester->execute([
            'file' => 'non_existent_file.csv'
        ]);
    }

    public function testCommandDescription(): void
    {
        $application = new Application();
        $command = new NrfcFixturesImportCommand($this->entityManager, $this->teamService, $this->clubRepository);
        $application->add($command);
        
        $this->assertEquals('Import data from CSV file and create entities', $command->getDescription());
        $this->assertNotEmpty($command->getHelp());
        
        $definition = $command->getDefinition();
        $this->assertTrue($definition->hasArgument('file'));
        $this->assertTrue($definition->hasOption('batch-size'));
    }

    public function testCommandHelp(): void
    {
        $this->commandTester->execute(
            ['file' => 'non_existent_file.csv', '--help' => true],
        );
        $this->assertEquals(Command::SUCCESS, $this->commandTester->getStatusCode());
    }

    public function testBadType(): void
    {
        $this->commandTester->execute(
            ['file' => 'non_existent_file.csv', '--type' => 'not-a-real-type'],
        );
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('[ERROR] Invalid type', trim($output));
        $this->assertEquals(Command::FAILURE, $this->commandTester->getStatusCode());
    }

    /**
     * @throws ExceptionInterface
     */
    public function testErrorInProcessRow(): void
    {
        // Doesn't really test anything, it's for coverage
        $this->clubRepository->expects($this->atLeastOnce())->method('findOneBy')->willThrowException(new BadMethodCallException());
        $this->commandTester->execute([
            'file' => $this->tempClubFile,
            '--type' => 'club',
        ]);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Error processing row', trim($output));
        $this->assertEquals(Command::SUCCESS, $this->commandTester->getStatusCode());
    }

    public function testErrorInTopLevel(): void
    {
        // Doesn't really test anything, it's for coverage
        $this->entityManager->expects($this->atLeastOnce())->method('flush')->willThrowException(new BadMethodCallException());
        $this->commandTester->execute([
            'file' => $this->tempClubFile,
            '--type' => 'club',
        ]);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Import failed', trim($output));
        $this->assertEquals(Command::FAILURE, $this->commandTester->getStatusCode());
    }
} 