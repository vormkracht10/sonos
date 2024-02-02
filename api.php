<?php

require 'vendor/autoload.php';

use duncan3dc\Sonos\Network;

$network = new Network();

$controllers = $network->getControllers();

foreach($controllers as $controller) {
    echo $controller->getStateDetails();
}
