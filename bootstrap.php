<?php

use Dotenv\Dotenv;
use Dotenv\Repository\RepositoryBuilder;
use Dotenv\Repository\Adapter\PutenvAdapter;
use Dotenv\Repository\Adapter\EnvConstAdapter;

require __DIR__ . '/vendor/autoload.php';

require __DIR__ . '/SonosData.php';

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
