<?php

require __DIR__ . '/bootstrap.php';

$sonos = new Sonos();

echo $sonos->run();
