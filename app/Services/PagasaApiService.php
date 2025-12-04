<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PagasaApiService
{
    /**
     * PAGASA API endpoints and data sources
     */
    private const PAGASA_BASE_URL = 'https://pubfiles.pagasa.dost.gov.ph';
    private const PAGASA_RSS_FEED = 'https://www.pagasa.dost.gov.ph/rss';
    private const WEATHER_API_URL = 'https://api.openweathermap.org/data/2.5';
    
    /**
     * Get current weather data for Metro Manila
     */
    public function getCurrentWeather()
    {
        return Cache::remember('pagasa_current_weather', 300, function () {
            try {
                // Using OpenWeatherMap API as proxy for Philippine weather data
                // In production, use official PAGASA API if available
                $response = Http::timeout(10)->get(self::WEATHER_API_URL . '/weather', [
                    'q' => 'Manila,PH',
                    'appid' => env('OPENWEATHER_API_KEY', 'demo'),
                    'units' => 'metric'
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return [
                        'temperature' => $data['main']['temp'] ?? null,
                        'humidity' => $data['main']['humidity'] ?? null,
                        'pressure' => $data['main']['pressure'] ?? null,
                        'wind_speed' => $data['wind']['speed'] ?? null,
                        'description' => $data['weather'][0]['description'] ?? null,
                        'updated_at' => now(),
                    ];
                }
            } catch (\Exception $e) {
                Log::error('PAGASA API Error: ' . $e->getMessage());
            }

            return $this->getFallbackWeatherData();
        });
    }

    /**
     * Get tropical cyclone information
     */
    public function getTropicalCyclones()
    {
        return Cache::remember('pagasa_tropical_cyclones', 600, function () {
            try {
                // PAGASA Tropical Cyclone Bulletin
                // Note: This is a placeholder. In production, parse actual PAGASA bulletins
                $response = Http::timeout(10)->get('https://www.pagasa.dost.gov.ph/tropical-cyclone/severe-weather-bulletin');
                
                if ($response->successful()) {
                    return $this->parseTropicalCycloneData($response->body());
                }
            } catch (\Exception $e) {
                Log::error('PAGASA Cyclone API Error: ' . $e->getMessage());
            }

            return [];
        });
    }

    /**
     * Get rainfall data
     */
    public function getRainfallData()
    {
        return Cache::remember('pagasa_rainfall', 300, function () {
            try {
                // PAGASA Rainfall data
                // Using weather forecast as proxy
                $response = Http::timeout(10)->get(self::WEATHER_API_URL . '/forecast', [
                    'q' => 'Manila,PH',
                    'appid' => env('OPENWEATHER_API_KEY', 'demo'),
                    'units' => 'metric',
                    'cnt' => 8 // 24 hours
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $rainfall = [];
                    
                    foreach ($data['list'] ?? [] as $forecast) {
                        $rainfall[] = [
                            'timestamp' => $forecast['dt'],
                            'rain' => $forecast['rain']['3h'] ?? 0,
                            'description' => $forecast['weather'][0]['description'] ?? '',
                        ];
                    }
                    
                    return $rainfall;
                }
            } catch (\Exception $e) {
                Log::error('PAGASA Rainfall API Error: ' . $e->getMessage());
            }

            return [];
        });
    }

    /**
     * Get flood warnings
     */
    public function getFloodWarnings()
    {
        return Cache::remember('pagasa_flood_warnings', 600, function () {
            // In production, parse actual PAGASA flood bulletins
            $rainfallData = $this->getRainfallData();
            $warnings = [];

            foreach ($rainfallData as $data) {
                if (($data['rain'] ?? 0) > 10) { // Heavy rainfall threshold
                    $warnings[] = [
                        'location' => 'Metro Manila',
                        'severity' => $data['rain'] > 20 ? 'high' : 'moderate',
                        'message' => 'Heavy rainfall expected. Flooding possible in low-lying areas.',
                        'timestamp' => $data['timestamp'],
                    ];
                }
            }

            return $warnings;
        });
    }

    /**
     * Parse tropical cyclone data from PAGASA bulletin
     */
    private function parseTropicalCycloneData($html)
    {
        // This would parse actual PAGASA HTML/XML data
        // Placeholder implementation
        return [];
    }

    /**
     * Fallback weather data when API is unavailable
     */
    private function getFallbackWeatherData()
    {
        return [
            'temperature' => null,
            'humidity' => null,
            'pressure' => null,
            'wind_speed' => null,
            'description' => 'Data temporarily unavailable',
            'updated_at' => now(),
            'source' => 'Cached/Fallback',
        ];
    }

    /**
     * Get weather advisory
     */
    public function getWeatherAdvisory()
    {
        return Cache::remember('pagasa_advisory', 1800, function () {
            try {
                // In production, fetch actual PAGASA advisories
                // This is a placeholder structure
                return [
                    'title' => 'Weather Advisory',
                    'content' => 'Monitor PAGASA official channels for latest updates.',
                    'issued_at' => now(),
                    'source' => 'PAGASA',
                ];
            } catch (\Exception $e) {
                Log::error('PAGASA Advisory API Error: ' . $e->getMessage());
            }

            return null;
        });
    }
}
