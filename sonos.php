<?php

use duncan3dc\Sonos\Network;
use GuzzleHttp\Client;

class Sonos
{
    public $network;

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
        $endpoints = [
            'https://vormkracht10-app.test/webhooks/sonos',
            'https://vormkracht10.app/webhooks/sonos',
        ];

        $client = new Client();

        $json = json_encode(['speakers' => json_encode($speakers)]);
        $passkey = getenv('SONOS_PASSKEY');

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
                echo $body;
            } catch (\Exception $e) {
                echo "Error occurred: " . $e->getMessage() . "\n";
            }
        }
    }
}
