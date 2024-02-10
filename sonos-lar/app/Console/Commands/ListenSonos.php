<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use duncan3dc\Sonos\Network;
use GuzzleHttp\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ListenSonos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sonos:listen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public $controllers;

    public $results;

    public $apiKey;

    //Dit moet ook de refresh time van de deamon worden om het php script te runnen
    public $expiration = 600;

    public $endpoint = 'https://vormkracht10-app.test/sonos/controller';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $network = new Network();
        $this->apiKey = config("services.imgbb.token");
        $this->controllers = $network->getControllers();
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
                $fileName = Str::random(40) . '.jpg';
                $imageContent = file_get_contents($albumArtUrl);

                $storedImagePath = Storage::put("images/$fileName", $imageContent);

                if ($storedImagePath) {
                    $result['imageFilePath'] = $storedImagePath;
                } else {
                    $result['imageFilePath'] = null;
                }
            } else {
                $result['imageFilePath'] = null;
            }

            $this->results[] = $result;
        }
        $this->last();
    }

    public function UploadImage($fileName)
    {
        $params = [
            'expiration' => $this->expiration,
            'key' => $this->apiKey,
        ];

        $client = new Client();

        try {
            $filePath = Storage::path("images/$fileName");
            $this->info($filePath);
            $response = $client->post('https://api.imgbb.com/1/upload', [
                'multipart' => [
                    [
                        'name' => 'image',
                        'contents' => fopen($filePath, 'r'),
                        'filename' => $fileName,
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


    public function last()
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
                echo "Error sending results to $endpoint: " . $e->getMessage() . PHP_EOL;
            }
        }
    }
}
