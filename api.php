<?php

require 'vendor/autoload.php';

use GuzzleHttp\Client;
use Illuminate\Support\Str;
use duncan3dc\Sonos\Network;
use Illuminate\Support\Carbon;

class Sonos
{
    public $controllers;
    public $results;
    public $apiKey = "";
    public function __construct()
    {
        $network = new Network();
        $this->controllers = $network->getControllers();
    }

    public function run()
    {
        $timezone = 'Europe/Amsterdam';
        $this->results = [];

        foreach ($this->controllers as $controller) {
            $stateDetails = $controller->getStateDetails();
            $result = [
                'speaker' => $controller->getRoom(),
                'title' => $stateDetails->getTitle(),
                'artist' => $stateDetails->getArtist(),
                'token' => hash('sha256', Carbon::now($timezone)->toDateTimeString()),
                'date' => Carbon::now($timezone)->toDateTimeString(),
            ];

            $albumArtUrl = $stateDetails->getAlbumArt();

            if (!empty($albumArtUrl)) {
                $fileName = Str::random(40);
                $destinationPathWithExtension = "images/{$fileName}.jpg";

                if (!file_exists("images")) {
                    mkdir("images");
                }

                $imageContent = file_get_contents($albumArtUrl);
                file_put_contents($destinationPathWithExtension, $imageContent);

                $imageUrl = $this->UploadImage($destinationPathWithExtension);

                if ($imageUrl) {
                    $result['imageFilePath'] = $imageUrl;
                } else {
                    $result['imageFilePath'] = null;
                }
            } else {
                $result['imageFilePath'] = null;
            }

            $this->results[] = $result;
        }
    }

    public function UploadImage($imagePath)
    {

        $params = [
            'expiration' => 600,
            'key' => $this->apiKey,
        ];

        $client = new Client();

        try {
            $response = $client->post('https://api.imgbb.com/1/upload', [
                'multipart' => [
                    [
                        'name' => 'image',
                        'contents' => fopen($imagePath, 'r'),
                    ],
                ],
                'query' => $params,
            ]);

            $responseData = json_decode($response->getBody(), true);

            if (isset($responseData['data']['url'])) {
                return $responseData['data']['url'];
            }

            return null;
        } catch (\Exception $e) {
            echo 'Error uploading image: ' . $e->getMessage();
            return null;
        }
    }

    public function __destruct()
    {
        $endpoints = [
            'https://vormkracht10-app.test/sonos/webhook',
            'https://vormkracht10.app/sonos/webhook',
        ];

        $client = new Client();

        foreach ($endpoints as $endpoint) {
            try {
                $response = $client->post($endpoint, [
                    'form_params' => ['results' => json_encode($this->results)],
                ]);

                echo "Results sent to $endpoint: " . $response->getBody() . PHP_EOL;
            } catch (\Exception $e) {
                // Handle exceptions, log errors, etc.
                echo "Error sending results to $endpoint: " . $e->getMessage() . PHP_EOL;
            }
        }
    }
}

$sonos = new Sonos();
$sonos->run();
