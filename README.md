<h1 align="center">
  <br>
  <img width="256" src="https://vormkracht10.nl/cdn/13f1e3fb-15c8-4655-bf9b-a85899694c45/-/format/auto/-/quality/smart/vk10-github.png" />
</h1>

<div align="center">
<h4 align="center" >Sonos - Vormkracht10</h4>
</div>


# Setting Up The Sonos Webhook Sender

This script helps set up the webhook sender system for Sonos.

## Prerequisites

Make sure you have `php^7.3` or `php^7.4`  installed on your system.

## Setup Steps

1. Open your terminal and navigate to `/var/www/`.

2. Enter the following command:
   ```bash
   git clone git@github.com:vormkracht10/sonos.git && cd sonos

3. Run the following command in your terminal to execute the setup of the webhook system:

   ```bash
   composer install && php setup.php

4. Insert `* * * * * /usr/bin/php /var/www/sonos/cron.php >> /var/www/sonos/cronlog/sonos.log 2>&1` in the crontab

   ```bash
   crontab -e
5. Enjoy!
