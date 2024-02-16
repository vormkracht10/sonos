<?php

require __DIR__ . '/bootstrap.php';

$sonos = new Sonos();

foreach([0, 10, 20, 30, 40, 50] as $seconds)
{
    $sonos->run();

    sleep(10);
}
