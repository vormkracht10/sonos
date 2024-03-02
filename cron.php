<?php

require __DIR__ . '/bootstrap.php';

$sonosDaa = new SonosData();
$sonosPause = new Pause();

while (true) {
    foreach (range(0, 30) as $seconds) {
        $sonosDaa->run();
        sleep(2);
        $sonosPause->run();
        sleep(0.5);
    }
}
