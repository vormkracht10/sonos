<?php

use duncan3dc\Sonos\Controller;
use GuzzleHttp\Client;
use duncan3dc\Sonos\Network;

class CheckForActions
{
    public $network;

    public function __construct()
    {
        $this->network = new Network;
    }


    public function run()
    {
        $client = new Client;

        $endpoint = getenv('SONOS_ENDPOINT') . '/webhooks/sonos/state';
        $response = $client->get($endpoint, [
            'verify' => false,
        ]);

        $result = $response->getBody()->getContents();

        $this->handleResult($result);
    }

    public function handleResult($result)
    {
        $array = json_decode($result, true)['speakers'] ?? [];

        foreach ($array as $controller => $actionContents) {
            $this->performAction($controller, $actionContents);
        }
    }

    public function performAction($controller, $actionContents)
    {
        $controllerIp = $controller;

        $action = $actionContents['custom_state'];

        if ($this->notAlreadyExecuted($actionContents['order_uuid'])) {
            $controller = $this->network->getControllerByIp($controllerIp);

            if ($controller instanceof Controller) {
                match ($action) {
                    'PLAYING' => $controller->play(),
                    'PAUSED' => $controller->pause(),
                    default => null
                };

                echo 'Performed action ' . $action . ' on ' . $controllerIp . PHP_EOL;
            }
        }
    }

    public function notAlreadyExecuted($orderUuid)
    {
        $directory = __DIR__ . '/orders/';
        $file = $directory . 'orders' . '.json';

        if (!file_exists($file)) {
            // Create File
            file_put_contents($file, json_encode([]));
        }

        $contents = file_get_contents($file);
        $json = json_decode($contents, true);

        if (in_array($orderUuid, $json)) {
            return false;
        } else {
            $json[] = $orderUuid;
            file_put_contents($file, json_encode($json));
            return true;
        }
    }
}
