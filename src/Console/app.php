<?php

declare(strict_types=1);

use Flipsite\Console\Commands;
use Symfony\Component\Console\Application;

require_once getenv('VENDOR_DIR').'/autoload.php';

$app      = new Application('Flipsite', 'v0.9');
$commands = Commands::getCommands(getenv('VENDOR_DIR').'/flipsite/flipsite/src/Console/Commands');
foreach ($commands as $class) {
    $app->add(new $class());
}

$app->run();
