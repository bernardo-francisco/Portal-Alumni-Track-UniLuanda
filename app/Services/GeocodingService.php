<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeocodingService
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.google.maps_api_key');
    }

    public function geocode(array $addressData)
    {
        $parts = array_filter([
            $addressData['address'] ?? '',
            $addressData['city'] ?? '',
            $addressData['province'] ?? '',
            $addressData['country'] ?? '',
        ]);
        
        if (empty($parts)) {
            return null;
        }
        
        $address = implode(', ', $parts);
        
        // Usando Nominatim (OpenStreetMap) como fallback quando não tem API key
        if (empty($this->apiKey)) {
            return $this->geocodeNominatim($address);
        }
        
        $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
            'address' => $address,
            'key' => $this->apiKey,
        ]);
        
        if ($response->successful() && $response->json('status') === 'OK') {
            $location = $response->json('results.0.geometry.location');
            return [
                'lat' => $location['lat'],
                'lng' => $location['lng'],
                'address' => $response->json('results.0.formatted_address'),
            ];
        }
        
        return $this->geocodeNominatim($address);
    }

    private function geocodeNominatim($address)
    {
        $response = Http::withHeaders([
            'User-Agent' => 'UniLuanda Alumni Track/1.0'
        ])->get('https://nominatim.openstreetmap.org/search', [
            'q' => $address,
            'format' => 'json',
            'limit' => 1,
        ]);
        
        if ($response->successful() && count($response->json()) > 0) {
            $data = $response->json()[0];
            return [
                'lat' => $data['lat'],
                'lng' => $data['lon'],
                'address' => $data['display_name'],
            ];
        }
        
        return null;
    }
}