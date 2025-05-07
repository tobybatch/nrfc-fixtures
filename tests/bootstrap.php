<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

$commands = [
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
// executes the "php bin/console cache:clear" command


$_SERVER['APP_ENV'] = 'test';
$_SERVER['APP_DEBUG'] = '0';
$_SERVER['KERNEL_CLASS'] = 'App\Tests\Kernel';
