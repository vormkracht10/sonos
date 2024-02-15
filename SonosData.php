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
        $tracks = $this->getNowPlayingTracks();

        $this->sendTracksToWebhooks($tracks);
    }

    public function getNowPlayingTracks()
    {
        $tracks = [];

        foreach ($this->network->getControllers() as $controller) {
            $stateDetails = $controller->getStateDetails();
            $tracks[] = [
                'volume' => $controller->getVolume(),
                'speaker' => $controller->getRoom(),
                'title' => $stateDetails->getTitle(),
                'artist' => $stateDetails->getArtist(),
                // 'cover' => $stateDetails->getAlbumArt() ? base64_encode(file_get_contents($stateDetails->getAlbumArt())) : null,
                'timestamp' => time(),
            ];
        }

        return $tracks;
    }

    public function sendTracksToWebhooks(array $tracks)
    {
        $endpoints = [
            'https://vormkracht10-app.test/webhooks/sonos',
            'https://vormkracht10.app/webhooks/sonos',
        ];

        $client = new Client();

        $json = json_encode(['tracks' => json_encode($tracks)]);
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
            } catch (\Exception $e) {
                echo "Error occurred: " . $e->getMessage() . "\n";
            }
        }
    }
}
