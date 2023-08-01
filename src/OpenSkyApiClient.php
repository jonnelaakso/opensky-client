<?php

namespace jonnelaakso\OpenskyApiClient;

class OpenSkyApiClient
{
    private $username;
    private $password;

    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    private function fetchAirplaneDataFromAPI($icao24)
    {
        $url = "https://opensky-network.org/api/states/all?icao24={$icao24}";
        $credentials = base64_encode("{$this->username}:{$this->password}");

        $context = stream_context_create([
            'http' => [
                'header' => "Authorization: Basic {$credentials}"
            ]
        ]);

        $response = file_get_contents($url, false, $context);

        if ($response === false) {
            throw new \RuntimeException('Failed to fetch data from the API.');
        }

        $data = json_decode($response, true);

        if (!$data || empty($data['states'])) {
            throw new \RuntimeException('No flight data available for the given airplane code.');
        }

        return $data['states'][0] ?? null;
    }

    public function getFlightInformation($icao24)
    {
        $flightData = $this->fetchAirplaneDataFromAPI($icao24);

        if (!$flightData) {
            throw new \RuntimeException('No flight data available for the given airplane code.');
        }

        $flightInformation = [
            'icao24' => $flightData[0],
            'callsign' => $flightData[1],
            'origin_country' => $flightData[2],
            'latitude' => $flightData[6],
            'longitude' => $flightData[5],
            'altitude' => $flightData[7],
            'velocity' => $flightData[9],
            'heading' => $flightData[10]
        ];

        return $flightInformation;
    }
}