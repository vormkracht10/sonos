#!/bin/bash

# Run the desired command every second
while true; do
    echo "Running collect_cron.php"
    php ../collect_cron.php
    sleep 1
done
