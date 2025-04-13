#!/bin/bash

# Run the desired command every second
while true; do
    echo "Running collect_cron.php"
    /Users/manoj/Library/Application\ Support/Herd/bin/php83 /private/var/www/sonos/collect_cron.php
    sleep 1
done
