#!/bin/bash

# Run the desired command every second
while true; do
    echo "Running actions_cron.php"
    php ../action_cron.php
    sleep 1
done
