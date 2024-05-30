<?php

use duncan3dc\Sonos\Network;
use GuzzleHttp\Client;
use Illuminate\Support\Carbon;

class SonosData
{
    public $network;

    public function __construct()
    {
        $this->network = new Network();
    }

    public function run()
    {
        $speakers = $this->getNowPlayingTracks();
        // var_dump($speakers);
        $this->sendTracksToWebhooks($speakers);
        $this->checkReboot();
    }
    private function checkReboot()
    {
        $devices = $this->network->getSpeakers();

        foreach ($devices as $device) {
            $device->getIp();
            echo "Rebooting speaker: " . $device->getIp() . "\n";
        }
    }
    public function getNowPlayingTracks()
    {
        foreach ($this->network->getControllers() as $controller) {
            $stateDetails = $controller->getStateDetails();
            $speakers[$controller->getIp()] = [
                'room' => $controller->getRoom(),
                'state' => $controller->getStateName(),
                'volume' => $controller->getVolume(),
                'title' => $stateDetails->getTitle(),
                'artist' => $stateDetails->getArtist(),
                'album' => $stateDetails->getAlbum(),
                'duration' => $stateDetails->getDuration()->asInt(),
                'position' => $stateDetails->getPosition()->asInt(),
                'cover' => $stateDetails->getAlbumArt(),
                'timestamp' => time(),
            ];
        }

        return $speakers;
    }

    public function sendTracksToWebhooks(array $speakers)
    {
        $client = new Client();

        $json = json_encode(['speakers' => $speakers]);
        $secret = getenv('SONOS_SECRET');
        $endpoints = explode(',', getenv('SONOS_ENDPOINT'));
        foreach ($endpoints as $endpoint) {
            $endpoint = $endpoint . '/webhooks/sonos';
            try {
                $hash = hash_hmac('sha256', $json, $secret);

                $response = $client->post($endpoint, [
                    'json' => $json,
                    'headers' => [
                        'X-Signature' => $hash,
                    ],
                ]);

                $statusCode = $response->getStatusCode();

                if ($statusCode == 200) {
                    echo 'Endpoint succesfully send to ' . $endpoint . "\n";
                    echo 'Body ' . $response->getBody() . "\n";
                }
            } catch (\Exception $e) {
                echo 'Endpoint faild to send to ' . $endpoint . "\n";
                echo 'Error occurred: ' . $e->getMessage() . "\n";
            }
        }
    }
}
