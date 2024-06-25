<?php

require __DIR__ . '/bootstrap.php';

$sonosData = new SonosData();

foreach (range(0, 60) as $seconds) {
    $sonosData->run();
    sleep(1);
}
