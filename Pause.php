<?php

use GuzzleHttp\Client;
use duncan3dc\Sonos\Network;
use Illuminate\Support\Carbon;
use duncan3dc\Sonos\Tracks\Track;
use duncan3dc\Sonos\Tracks\TextToSpeech;
use GuzzleHttp\Exception\GuzzleException;
use duncan3dc\Sonos\Utils\Directory;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Duncan3dc\Sonos\Controller;

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

                    // Ensure $data is not null
                    if ($data) {
                        // Check if custom_state is set
                        if (isset($data->custom_state)) {
                            // Use switch statement for cleaner code
                            switch ($data->custom_state) {
                                case "PAUSED_PLAYBACK":
                                    echo "Pausing the hell out of $controller_ip\n";
                                    $controller_home->setState(203);
                                    break;
                                case "PLAYING":
                                    echo "Playing the hell out of $controller_ip\n";
                                    $controller_home->setState(202);
                                    break;
                                default:
                                    echo "Unknown custom state: $data->custom_state\n";
                            }
                            echo "Match.....\n\n\n\n\n\n\n";
                        } else {
                            echo "No custom_state for controller: $controller_ip\n";
                        }
                    } else {
                        echo "No data available for controller: $controller_ip\n";
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
            echo "Error: " . $e->getMessage();
        }

        return $result;
    }
}
