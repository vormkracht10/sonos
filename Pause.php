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

        foreach ($this->controllers as $controller_root) {
            $controller_name = $controller_root->getRoom();
            foreach ($data as $controller => $input) {
                if ($controller_name === $controller) {
                    $current_state = $controller_root->getState();
                    if ($input['state'] === "PAUSED_PLAYBACK" && $current_state !== 'PAUSED_PLAYBACK') {
                        $controller_root->pause();
                    } elseif ($input['state'] === "PLAYBACK" && $current_state !== 'PLAYING') {
                        $controller_root->play();
                    }
                }
            }
        }
    }


    public function getInfo()
    {
        $client = new Client();
        $request = new \GuzzleHttp\Psr7\Request('GET', $this->endpoint);
        $result = null;
        try {
            $response = $client->send($request);
            $result = $response->getBody()->getContents();
        } catch (GuzzleException $e) {
            echo "Error: " . $e->getMessage();
        }

        return $result;
    }
}
