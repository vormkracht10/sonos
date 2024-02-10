<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use duncan3dc\Sonos\Network;
use Illuminate\Support\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class UpdateSonos extends Command
{
    protected $signature = 'sonos:update';
    protected $description = 'Update Sonos controller with volume information';

    public function handle()
    {
        $date = Carbon::now();

        $network = new Network();
        $controllers = $network->getControllers();
        $firstEndpoint = 'https://vormkracht10.app/sonos/controller';
        $secondEndpoint = 'https://vormkracht10-app.test/sonos/controller';

        $client = new Client();

        try {
            $response = $client->get($firstEndpoint);
            $data = json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            try {
                $response = $client->get($secondEndpoint);
                $data = json_decode($response->getBody(), true);
            } catch (RequestException $e) {
                echo "Both endpoints failed to respond.";
                return;
            }
        }
        $this->info(print_r($data));

        if (!empty($controllers)) {
            foreach ($controllers as $controller) {
                $stateDetails = $controller->getStateDetails();
                $speaker = $controller->getRoom();
                if (isset($data['Volume'])) {
                    $date = $data['Date'];
                    $current_time = time();
                    $given_time = strtotime($date);
                    $diffSeconds = $current_time - $given_time;
                    if ($diffSeconds <= 20) {
                        if ($controller->setVolume($data['Volume'])) {
                            $this->info('Sonos controller updated successfully.');
                        } else {
                            $this->info('Sonos controller update was not successful.');
                        }
                    } else {
                        $this->info('Sonos controller: volume information is too old.');
                    }
                } else {
                    $this->info('Sonos controller: no volume information available.');
                }
            }
        } else {
            $this->info('No Sonos controllers found.');
        }
    }
}
