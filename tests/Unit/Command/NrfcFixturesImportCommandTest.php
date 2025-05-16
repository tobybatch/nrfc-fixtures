<?php

namespace App\Tests\Command;

use App\Command\NrfcFixturesImportCommand;
use App\Entity\Club;
use App\Entity\Fixture;
use App\Repository\ClubRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
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
        // Create a temporary CSV file
        $this->tempFile = tempnam(sys_get_temp_dir(), 'test_');
        $fh = fopen($this->tempFile, 'w');
        fputcsv($fh, ["","","Mini","U13","U14","U15","U16","U17/U18 (COLTS)","","GIRLS U12","GIRLS U14","GIRLS U16","GIRLS U18"]);
        fputcsv($fh, ["01-Jan-23","xxx","Training","Training","Training","Training","Training","Training","Training","Training","Training","Training","Training","Training"]);
        fputcsv($fh, ["01-Feb-30","xxx","Training","Training","Training","Training","Training","Training","Training","Training","Training","Training","Training","Training"]);
        fputcsv($fh, ["01-Mar-30","xxx","Training","Training","Training","Training","Training","Training","Training","Training","Training","Training","Training","Training"]);
        fputcsv($fh, ["01-Apr-30","xxx","Training","Training","Training","Training","Training","Training","Training","Training","Training","Training","Training","Training"]);
        fputcsv($fh, ["01-May-30","xxx","Training","Training","Training","Training","Training","Training","Training","Training","Training","Training","Training","Training"]);
        fclose($fh);

        // Mock the club repository to return a club
        $club = new Club();
        $club->setName('Test Club');
        $this->clubRepository->method('findOneBy')->willReturn($club);
    }
    
    public function tearDown(): void
    {
        unlink($this->tempFile);
        
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
        $this->commandTester->execute([
            'file' => $this->tempFile,
            '--skip-first' => false,
            '--batch-size' => 10
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Successfully imported', $output);
        $this->assertEquals(0, $this->commandTester->getStatusCode());

        // Mock the entity manager
        // TODO check these asserts
//        $this->entityManager->expects($this->atLeastOnce())
//            ->method('persist')
//            ->with($this->isInstanceOf(Fixture::class));
//        $this->entityManager->expects($this->atLeastOnce())
//            ->method('flush');
//        $this->entityManager->expects($this->atLeastOnce())
//            ->method('clear');
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
} 