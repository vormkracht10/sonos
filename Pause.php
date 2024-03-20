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
        $controllers_app = json_decode($this->getInfo());

        if ($controllers_app && isset($controllers_app->speakers)) {
            foreach ($this->controllers as $controller_home) {
                $controller_ip = $controller_home->getIp();

                if (isset($controllers_app->speakers->$controller_ip)) {
                    $data = $controllers_app->speakers->$controller_ip;
                    if ($data) {
                        if (isset($data->custom_state)) {
                            $date = Carbon::parse($data->timestamp);
                            $current_time = Carbon::now();
                            if ($date->diffInSeconds($current_time) > 10) {
                                continue;
                            }
                            switch ($data->custom_state) {
                                case 'PAUSED_PLAYBACK':
                                    echo "Pausing the hell out of $controller_ip\n";
                                    $controller_home->setState(203);
                                    break;
                                case 'PLAYING':
                                    echo "Playing the hell out of $controller_ip\n";
                                    $controller_home->setState(202);
                                    break;
                                default:
                                    echo "Unknown custom state: $data->custom_state\n";
                            }
                        }
                        echo "Match.....\n\n\n\n\n\n\n";
                    } else {
                        echo "No custom_state for controller: $controller_ip\n";
                    }
                } else {
                    echo "No data available for controller: $controller_ip\n";
                }
            }
        } else {
            echo "No speakers data available.\n";
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
            echo 'Error: ' . $e->getMessage();
        }

        return $result;
    }
}
