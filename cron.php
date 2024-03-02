<?php

require __DIR__ . '/bootstrap.php';

$sonosDaa = new SonosData();
// $sonosPause = new Pause();

foreach (range(0, 60) as $seconds) {
    $sonosDaa->run();
    // try {
    //     $sonosPause->run();
    // } catch (Exception $e) {

    //     echo "$e";
    // }
    sleep(1);
}
