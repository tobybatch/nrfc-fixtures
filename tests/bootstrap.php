<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

$commands = [
    'doctrine:database:drop -n',
    'doctrine:database:create -n',
    'doctrine:migrations:migrate -n',
    'doctrine:fixtures:load -n',
];

foreach ($commands as $command) {
    passthru(sprintf(
        'APP_ENV=%s php "%s/../bin/console" %s',
        $_ENV['APP_ENV'],
        __DIR__,
        $command
    ));
}

// Create users

$_SERVER['APP_ENV'] = 'test';
$_SERVER['APP_DEBUG'] = '0';
$_SERVER['KERNEL_CLASS'] = 'App\Tests\Kernel';
