<?php

require __DIR__ . '/bootstrap.php';

$sonosData = new SonosData();
$sonosPause = new Pause();

foreach (range(0, 1000000) as $seconds) {
    // $sonosData->run();
    // try {
    $sonosPause->run();
    // } catch (Exception $e) {
    //     echo "$e";
    // }
    sleep(1);
}
