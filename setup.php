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
    $data = "SONOS_PASSKEY=" . trim(fgets(STDIN));
    touch($envFilePath);
    file_put_contents($envFilePath, $data);

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
