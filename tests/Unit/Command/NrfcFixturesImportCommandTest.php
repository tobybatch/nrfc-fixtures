<?php

namespace App\Tests\Unit\Command;

use App\Command\NrfcFixturesImportCommand;
use App\Entity\Club;
use App\Entity\Fixture;
use App\Repository\ClubRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\BrowserKit\Exception\BadMethodCallException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;


class NrfcFixturesImportCommandTest extends TestCase
{
    private CommandTester $commandTester;
    private EntityManagerInterface $entityManager;
    private ClubRepository $clubRepository;
    private string $tempFixtureFile = __DIR__ . '/../../../assets/fixtures.csv';
    private string $tempClubFile = __DIR__ . '/../../../assets/clubs.csv';

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

    public function testExecuteWithValidFixturesFile(): void
    {
        $this->commandTester->execute([
            'file' => $this->tempFixtureFile,
            '--skip-first' => true,
            '--batch-size' => 2
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

    public function testExecuteWithValidClubsFile(): void
    {
        $this->commandTester->execute([
            'file' => $this->tempClubFile,
            '--type' => 'club',
            '--skip-first' => false,
            '--batch-size' => 10
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Successfully imported', $output);
        $this->assertEquals(0, $this->commandTester->getStatusCode());
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
        // $this->assertTrue($definition->hasOption('delimiter'));
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

    public function testFindClubEmptyName() :void
    {
        $command = new NrfcFixturesImportCommand($this->entityManager, $this->clubRepository);
        $reflection = new ReflectionClass($command);

        // set accessible for private method
        $method = $reflection->getMethod('findClub');
        $method->setAccessible(true);

        $result = $method->invoke($command, '');
        $this->assertNull($result);
    }

    /**
     * @throws \ReflectionException
     */
    public function testFindClubNameFixer() :void
    {
        $command = new NrfcFixturesImportCommand($this->entityManager, $this->clubRepository);
        $reflection = new ReflectionClass($command);

        // set accessible for private method
        $method = $reflection->getMethod('findClub');
        $method->setAccessible(true);

        $result = $method->invoke($command, 'W Norfolk');
        $this->assertNull($result);

        $result = $method->invoke($command, 'N Walsham');
        $this->assertNull($result);
    }
} 