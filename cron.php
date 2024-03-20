<?php

require __DIR__.'/bootstrap.php';

$sonosData = new SonosData();
$sonosPause = new Pause();

foreach (range(0, 60) as $seconds) {
    $sonosData->run();
    $sonosPause->run();
    sleep(1);
}
