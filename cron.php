<?php

require __DIR__.'/bootstrap.php';

foreach (range(0, 1000000) as $seconds) {
    (new SonosData)->run();
    sleep(1);
}
