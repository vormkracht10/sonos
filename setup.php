<?php


$filePath = '.env';

if (!file_exists($filePath) || filesize($filePath) === 0) {
    echo 'Go to Rocketeers => Sites => Vormkracht10 App => .env => search for "SONOS_PASSKEY="' . "\n";
    echo 'Enter the "SONOS_PASSKEY" value: ';
    $data = "SONOS_PASSKEY=" . trim(fgets(STDIN));




    touch($filePath);
    file_put_contents($filePath, $data);

    echo "Env created";
} else {
    echo ".env already satisfied";
}
