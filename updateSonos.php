<?php

require 'vendor/autoload.php';

use GuzzleHttp\Client;
use duncan3dc\Sonos\Network;
use Illuminate\Support\Carbon;

class Sonos
{
    public $controllers;

    public $results;

    public $apiKey = '';

    public $endpoint = 'https://vormkracht10-app.test/sonos/controller';
    public function __construct()
    {
        $network = new Network();
        $this->controllers = $network->getControllers();
    }

    public function run()
    {
        $data = json_decode($this->getInfo(), true);
        $currentDate = Carbon::now();

        foreach ($this->controllers as $controller) {
            $stateDetails = $controller->getStateDetails();
            $speaker = $controller->getRoom();
            if (isset($data['Volume'])) {
                $volumeDate = Carbon::parse($data['Volume']);
                if ($volumeDate->diffInSeconds($currentDate) <= 20) {
                    $controller->setVolume($data['Volume']);
                }
            }
        }
    }

    public function getInfo()
    {
        $client = new Client();

        $request = new \GuzzleHttp\Psr7\Request('GET', $this->endpoint);

        $result = null;

        $promise = $client->sendAsync($request)->then(function ($response) use (&$result) {
            $result = $response->getBody();
        });

        $promise->wait();

        return $result;
    }
}




//Emergency Volume 
$sonos = new Sonos();
$sonos->run();
