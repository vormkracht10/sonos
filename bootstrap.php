<?php

use Dotenv\Dotenv;
use Dotenv\Repository\Adapter\EnvConstAdapter;
use Dotenv\Repository\Adapter\PutenvAdapter;
use Dotenv\Repository\RepositoryBuilder;

require __DIR__ . '/vendor/autoload.php';

require __DIR__ . '/sonos.php';

$filePath = '.env';

if (file_exists($filePath) || filesize($filePath) > 0) {
    $repository = RepositoryBuilder::createWithNoAdapters()
        ->addAdapter(EnvConstAdapter::class)
        ->addWriter(PutenvAdapter::class)
        ->immutable()
        ->make();

    $dotenv = Dotenv::create($repository, __DIR__);
    $dotenv->load();
} else {
    echo "First run `php setup.php`";
}
