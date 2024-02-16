<?php


$envFilePath = '.env';
$logDirectory = 'cronlog';
$logFilePath = $logDirectory . "/sonos.log";

if (!is_dir($logDirectory)) {
    mkdir($logDirectory, 0777, true);
    touch($logFilePath);
}

if (!file_exists($envFilePath) || filesize($envFilePath) === 0) {
    echo 'Go to Rocketeers => Sites => Vormkracht10 App => .env => search for "SONOS_PASSKEY="' . "\n";
    echo 'Enter the "SONOS_PASSKEY" value: ';
    $passkey = trim(fgets(STDIN));

    $entry = "SONOS_PASSKEY=" . $passkey . "\n";

    echo "Enter the endpoints for your Sonos webhook (separated by spaces): ";
    $endpointsInput = trim(fgets(STDIN));

    $endpoints = explode(' ', $endpointsInput);

    if (count($endpoints) > 0) {
        $data['endpoints'] = $endpoints;
        $entry .= "SONOS_ENDPOINTS='" . json_encode($data['endpoints']) . "'";
    } else {
        echo "No endpoints entered. Delete the .env and the cornlog directory. After that restart the script";
        exit();
    }

    file_put_contents($envFilePath, $entry);
    $commands = "herd use php@7.4 && composer install || /usr/bin/php composer install";
    shell_exec($commands);
    echo "Env created\n";
} else {
    echo ".env already satisfied\n";
}



$newCrontabEntry = '* * * * * /usr/bin/php /var/www/sonos/cron.php >> /var/www/sonos/cronlog/sonos.log 2>&1';

$existingCrontab = shell_exec('crontab -l');

if (strpos($existingCrontab, $newCrontabEntry) === false) {
    $updatedCrontab = $existingCrontab . "\n" . $newCrontabEntry;
    $tempFile = tempnam(sys_get_temp_dir(), 'crontab');
    file_put_contents($tempFile, $updatedCrontab);
    shell_exec('crontab ' . $tempFile);
    unlink($tempFile);
    echo "New crontab entry added successfully\n";
} else {
    echo "Crontab entry already exists\n";
}
