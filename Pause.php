<?php

use duncan3dc\Sonos\Network;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Carbon;

class Pause
{
    public $controllers;

    public $results;

    public $apiKey = '';

    protected $endpoint;

    public function __construct()
    {
        $network = new Network();
        $this->controllers = $network->getControllers();
        $this->endpoint = getenv('SONOS_ENDPOINT') . '/webhooks/sonos/state';
    }

    public function run()
    {
        $json = $this->getInfo();
        $data = json_decode($json, true);
        $working_controllers = $this->possible($this->controllers);

        foreach ($data as $controller_name => $input) {
            // if (in_array($controller_name, $working_controllers)) {
            foreach ($this->controllers as $controller) {
                if ($controller_name === $controller->getRoom()) {
                    echo "\nHeyyy speaker $controller_name\n";
                    if ($input['state'] === 'PLAYING') {
                        try {
                            $controller->pause();
                        } catch (\duncan3dc\Sonos\Exceptions\SoapException $e) {
                            echo "Error pausing: " . $e->getMessage();
                        }
                    } elseif ($input['state'] === 'PAUSED_PLAYBACK') {
                        echo "Resuming playback\n\n";
                        $controller->play();
                    } else {
                        echo "No action required\n\n";
                    }
                }
            }
        }
    }
    public function possible($allControllers)
    {
        $names = [];
        foreach ($allControllers as $controller) {
            $names[] = $controller->getRoom();
        }

        $explain = [];
        if (in_array('Boiler Room', $names)) {
            $controller = $this->findControllerByName($allControllers, 'Boiler Room');
            if ($controller !== null && $controller->getQueue() !== null) {
                $explain[] = "Boiler Room";
            }
        }

        if (in_array('Glass Room', $names)) {
            $controller = $this->findControllerByName($allControllers, 'Glass Room');
            if ($controller !== null && $controller->getQueue() !== null) {
                $explain[] = "Glass Room";
            }
        }

        if (in_array('Office', $names)) {
            $controller = $this->findControllerByName($allControllers, 'Office');
            if ($controller !== null && $controller->getQueue() !== null) {
                $explain[] = "Office";
            }
        }

        return $explain;
    }

    private function findControllerByName($controllers, $name)
    {
        foreach ($controllers as $controller) {
            if ($controller->getRoom() === $name) {
                return $controller;
            }
        }
        return null;
    }

    public function getInfo()
    {
        try {
            $client = new Client();
            $request = new \GuzzleHttp\Psr7\Request('GET', $this->endpoint);
            $result = null;
            $promise = $client->sendAsync($request)->then(function ($response) use (&$result) {
                $result = $response->getBody();
            });
            $promise->wait();

            return $result;
        } catch (GuzzleException $e) {
            $info["Boiler Room"] = [
                'state' => "PLAYING",
                'date' => Carbon::now(),
            ];
            $catch = json_encode($info);
            return $catch;
        }
    }
}
