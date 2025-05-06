// tests/TestDatabaseSetup.php
<?php

use App\Kernel;

require __DIR__.'/../vendor/autoload.php';

$kernel = new Kernel('test', true);
$kernel->boot();

$application = new \Symfony\Bundle\FrameworkBundle\Console\Application($kernel);
$application->setAutoExit(false);

// Run migrations
$application->run(new \Symfony\Component\Console\Input\ArrayInput([
    'command' => 'doctrine:migrations:migrate',
    '--no-interaction' => true,
    '--env' => 'test',
]));

// Load fixtures if you have them
$application->run(new \Symfony\Component\Console\Input\ArrayInput([
    'command' => 'doctrine:fixtures:load',
    '--no-interaction' => true,
    '--env' => 'test',
]));