<?php

namespace App\Tests\Command;

use App\Command\NrfcFixturesImportCommand;
use App\Entity\Club;
use App\Entity\Fixture;
use App\Repository\ClubRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class NrfcFixturesImportCommandTest extends TestCase
{
    private CommandTester $commandTester;
    private EntityManagerInterface $entityManager;
    private ClubRepository $clubRepository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->clubRepository = $this->createMock(ClubRepository::class);

        $application = new Application();
        $application->add(new NrfcFixturesImportCommand($this->entityManager, $this->clubRepository));
        
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

    public function testExecuteWithValidFile(): void
    {
        // Create a temporary CSV file
        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        $csvContent = "01-Jan-23,Team,Training,Training,Training,Training,Training,Training,Training,Training,Training,Training,Training,Training\n";
        file_put_contents($tempFile, $csvContent);

        // Mock the club repository to return a club
        $club = new Club();
        $club->setName('Test Club');
        $this->clubRepository->method('findOneBy')->willReturn($club);

        // Mock the entity manager
        $this->entityManager->expects($this->atLeastOnce())
            ->method('persist')
            ->with($this->isInstanceOf(Fixture::class));
        $this->entityManager->expects($this->atLeastOnce())
            ->method('flush');
        $this->entityManager->expects($this->atLeastOnce())
            ->method('clear');

        $this->commandTester->execute([
            'file' => $tempFile,
            '--skip-first' => false,
            '--batch-size' => 10
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Successfully imported', $output);
        $this->assertEquals(0, $this->commandTester->getStatusCode());

        // Clean up
        unlink($tempFile);
    }

    public function testExecuteWithInvalidRow(): void
    {
        // Create a temporary CSV file with an invalid row
        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        $csvContent = "01-Jan-23,Team,Invalid Data\n";
        file_put_contents($tempFile, $csvContent);

        $this->commandTester->execute([
            'file' => $tempFile,
            '--skip-first' => true
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Successfully imported 0 records', $output);
        $this->assertEquals(0, $this->commandTester->getStatusCode());

        // Clean up
        unlink($tempFile);
    }

    public function testCommandDescription(): void
    {
        $application = new Application();
        $command = new NrfcFixturesImportCommand($this->entityManager, $this->clubRepository);
        $application->add($command);
        
        $this->assertEquals('Import data from CSV file and create entities', $command->getDescription());
        $this->assertNotEmpty($command->getHelp());
        
        $definition = $command->getDefinition();
        $this->assertTrue($definition->hasArgument('file'));
        $this->assertTrue($definition->hasOption('delimiter'));
        $this->assertTrue($definition->hasOption('skip-first'));
        $this->assertTrue($definition->hasOption('batch-size'));
    }
} 