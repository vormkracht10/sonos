<?php

use duncan3dc\Sonos\Network;
use GuzzleHttp\Client;

class Sonos
{
    public $network;
    public $endpoints;

    public function __construct()
    {
        $this->network = new Network();
    }

    public function run()
    {
        $speakers = $this->getNowPlayingTracks();

        $this->sendTracksToWebhooks($speakers);
    }

    public function getNowPlayingTracks()
    {
        foreach ($this->network->getControllers() as $controller) {
            $stateDetails = $controller->getStateDetails();

            $speakers[$controller->getRoom()] = [
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
        $passkey = getenv('SONOS_PASSKEY');
        $endpoints = json_decode(getenv("SONOS_ENDPOINTS"));
        foreach ($endpoints as $endpoint) {
            try {
                $hash = hash_hmac('sha256', $json, $passkey);
                $response = $client->post($endpoint, [
                    'json' => $json,
                    'headers' => [
                        'X-Signature' => $hash,
                    ]
                ]);

                $statusCode = $response->getStatusCode();
                $body = $response->getBody();
                if ($statusCode == 200) {
                    echo "Endpoint succesfully send to " . $endpoint . "\n";
                }
            } catch (\Exception $e) {
                echo "Endpoint faild to send to " . $endpoint . "\n";
                echo "Error occurred: " . $e->getMessage() . "\n";
            }
        }
    }
}
