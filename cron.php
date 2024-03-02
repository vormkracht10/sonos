<?php

require __DIR__ . '/bootstrap.php';

$sonosDaa = new SonosData();
$sonosPause = new Pause();

foreach (range(0, 60) as $seconds) {
    $sonosPause->run();
    $sonosDaa->run();
    $sonosPause->run();
}
