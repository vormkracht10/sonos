<?php

require __DIR__.'/bootstrap.php';

use duncan3dc\Sonos\Network;
use GuzzleHttp\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Sonos
{
    public $network;

    public $endpoint = 'https://vormkracht10-app.test/sonos/controller';


    public function __construct(Network $network) {}

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
            
            $tracks = [
                'speaker' => $controller->getRoom(),
                'title' => $stateDetails->getTitle(),
                'artist' => $stateDetails->getArtist(),
                'cover' => $stateDetails->getAlbumArt() ? base64_encode(file_get_contents($stateDetails->getAlbumArt())) : null,
                'timestamp' => time(),
            ];
        }

        return $tracks;
    }

    public function sendTracksToWebhooks(array $tracks)
    {
        $endpoints = [
            'https://vormkracht10-app.test/webhooks/sonos,
            'https://vormkracht10.app/webhooks/sonos',
        ];

        $client = new Client();

        $json = json_encode(['tracks' => json_encode($tracks)]);

        foreach ($endpoints as $endpoint) {
            $response = $client->post($endpoint, [
                'json' => $json,
                'X-Signature' => hash_hmac('sha256', $json, $signingSecret),
            ]);
        }
    }
}

$sonos = new Sonos();
$sonos->run();
