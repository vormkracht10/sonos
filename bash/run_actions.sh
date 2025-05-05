#!/bin/bash

# Run the desired command every second
while true; do
    echo "Running actions_cron.php"
    php "/home/vormkracht10/VK10/sonos-v1/action_cron.php"
    sleep 1
done
