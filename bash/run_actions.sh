#!/bin/bash

# Run the desired command every second
while true; do
    echo "Running actions_cron.php"
    /Users/manoj/Library/Application\ Support/Herd/bin/php83 /private/var/www/sonos/action_cron.php
    sleep 1
done
