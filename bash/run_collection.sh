#!/bin/bash

# Run the desired command every second

while true; do
    echo "Running collect_cron.php"
    php "/home/vormkracht10/VK10/sonos-v1/collect_cron.php"
    sleep 1
done
