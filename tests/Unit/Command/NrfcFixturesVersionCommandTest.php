<?php

namespace App\Tests\Command;

use App\Command\NrfcFixturesVersionCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class NrfcFixturesVersionCommandTest extends TestCase
{
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $application = new Application();
        $application->add(new NrfcFixturesVersionCommand());
        
        $command = $application->find('nrfc:fixtures:version');
        $this->commandTester = new CommandTester($command);
    }

    public function testExecute(): void
    {
        $this->commandTester->execute([]);
        
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('0.0.1', $output);
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testCommandDescription(): void
    {
        $this->commandTester->execute(['--help' => true]);
        
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Receive version information', $output);
        $this->assertStringContainsString('This command allows you to fetch various version information about the app', $output);
    }
} 