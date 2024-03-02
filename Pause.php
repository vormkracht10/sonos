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
        foreach ($data as $controller_name => $input) {
            print_r($input);
            foreach ($this->controllers as $controller) {
                $speaker = $controller->getRoom();
                if ($controller_name === $speaker) {
                    $inputDate = $input['date'];
                    if ($inputDate && (Carbon::parse($inputDate)->gt(Carbon::now()->subSeconds(10)))) {
                        if ($input['state'] === 'PAUSED_PLAYBACK') {
                            echo 'Pausing';
                            $controller->pause();
                        } else {
                            echo 'Playing';
                            if ($controller->getState()) {
                                $controller->play();
                            }
                        }
                    } else {
                        echo 'To long ago';
                    }
                } else {
                    echo "Speaker $controller_name not found";
                }
            }
        }
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
