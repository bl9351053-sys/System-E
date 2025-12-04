<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PhivolcsApiService
{
    /**
     * PhiVolcs API endpoints and data sources
     */
    private const PHIVOLCS_BASE_URL = 'https://earthquake.phivolcs.dost.gov.ph';
    private const USGS_EARTHQUAKE_API = 'https://earthquake.usgs.gov/fdsnws/event/1/query';
    
    /**
     * Get recent earthquakes in the Philippines
     */
    public function getRecentEarthquakes($limit = 10)
    {
        return Cache::remember('phivolcs_earthquakes', 300, function () use ($limit) {
            try {
                // Using USGS API for Philippine earthquakes
                // Coordinates for Philippines: latitude 4.5 to 21.5, longitude 116 to 127
                $response = Http::timeout(10)->get(self::USGS_EARTHQUAKE_API, [
                    'format' => 'geojson',
                    'starttime' => now()->subDays(7)->toIso8601String(),
                    'endtime' => now()->toIso8601String(),
                    'minlatitude' => 4.5,
                    'maxlatitude' => 21.5,
                    'minlongitude' => 116,
                    'maxlongitude' => 127,
                    'minmagnitude' => 2.5,
                    'orderby' => 'time',
                    'limit' => $limit
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return $this->formatEarthquakeData($data);
                }
            } catch (\Exception $e) {
                Log::error('PhiVolcs Earthquake API Error: ' . $e->getMessage());
            }

            return [];
        });
    }

    /**
     * Format earthquake data from USGS
     */
    private function formatEarthquakeData($data)
    {
        $earthquakes = [];

        foreach ($data['features'] ?? [] as $feature) {
            $properties = $feature['properties'];
            $geometry = $feature['geometry'];

            $earthquakes[] = [
                'magnitude' => $properties['mag'],
                'location' => $properties['place'],
                'latitude' => $geometry['coordinates'][1],
                'longitude' => $geometry['coordinates'][0],
                'depth' => $geometry['coordinates'][2],
                'timestamp' => date('Y-m-d H:i:s', $properties['time'] / 1000),
                'felt_reports' => $properties['felt'] ?? 0,
                'tsunami_warning' => $properties['tsunami'] ?? 0,
                'significance' => $this->calculateSignificance($properties['mag'], $geometry['coordinates'][2]),
                'source' => 'PhiVolcs/USGS',
            ];
        }

        return $earthquakes;
    }

    /**
     * Calculate earthquake significance
     */
    private function calculateSignificance($magnitude, $depth)
    {
        if ($magnitude >= 7.0) return 'critical';
        if ($magnitude >= 6.0) return 'high';
        if ($magnitude >= 5.0) return 'moderate';
        return 'low';
    }

    /**
     * Get active fault lines near location
     */
    public function getNearbyFaultLines($latitude, $longitude, $radiusKm = 50)
    {
        return Cache::remember("phivolcs_faults_{$latitude}_{$longitude}", 3600, function () use ($latitude, $longitude, $radiusKm) {
            // Known major fault lines in the Philippines
            $faultLines = [
                [
                    'name' => 'West Valley Fault',
                    'latitude' => 14.5995,
                    'longitude' => 121.0537,
                    'length_km' => 100,
                    'type' => 'Active',
                    'last_movement' => '1658',
                    'risk_level' => 9,
                ],
                [
                    'name' => 'East Valley Fault',
                    'latitude' => 14.6760,
                    'longitude' => 121.1144,
                    'length_km' => 10,
                    'type' => 'Active',
                    'last_movement' => 'Unknown',
                    'risk_level' => 7,
                ],
                [
                    'name' => 'Marikina Valley Fault System',
                    'latitude' => 14.6507,
                    'longitude' => 121.1029,
                    'length_km' => 146,
                    'type' => 'Active',
                    'last_movement' => '1658',
                    'risk_level' => 9,
                ],
                [
                    'name' => 'Philippine Fault Zone',
                    'latitude' => 14.5,
                    'longitude' => 121.0,
                    'length_km' => 1200,
                    'type' => 'Active',
                    'last_movement' => '1990',
                    'risk_level' => 8,
                ],
            ];

            // Filter by distance
            $nearbyFaults = [];
            foreach ($faultLines as $fault) {
                $distance = $this->calculateDistance(
                    $latitude,
                    $longitude,
                    $fault['latitude'],
                    $fault['longitude']
                );

                if ($distance <= $radiusKm) {
                    $fault['distance_km'] = round($distance, 2);
                    $nearbyFaults[] = $fault;
                }
            }

            return $nearbyFaults;
        });
    }

    /**
     * Get volcano status
     */
    public function getVolcanoStatus()
    {
        return Cache::remember('phivolcs_volcanoes', 3600, function () {
            // Active volcanoes in the Philippines with current status
            return [
                [
                    'name' => 'Taal Volcano',
                    'latitude' => 14.0021,
                    'longitude' => 120.9937,
                    'alert_level' => 1,
                    'status' => 'Abnormal',
                    'last_eruption' => '2022-03-26',
                    'description' => 'Low-level unrest. Possible phreatic or phreatomagmatic eruptions.',
                ],
                [
                    'name' => 'Mayon Volcano',
                    'latitude' => 13.2572,
                    'longitude' => 123.6856,
                    'alert_level' => 0,
                    'status' => 'Normal',
                    'last_eruption' => '2018-01-22',
                    'description' => 'No magmatic eruption imminent.',
                ],
                [
                    'name' => 'Kanlaon Volcano',
                    'latitude' => 10.4120,
                    'longitude' => 123.1320,
                    'alert_level' => 1,
                    'status' => 'Abnormal',
                    'last_eruption' => '2023-06-03',
                    'description' => 'Low-level unrest.',
                ],
            ];
        });
    }

    /**
     * Get tsunami advisory
     */
    public function getTsunamiAdvisory()
    {
        return Cache::remember('phivolcs_tsunami', 600, function () {
            try {
                // Check recent earthquakes for tsunami potential
                $earthquakes = $this->getRecentEarthquakes(5);
                
                foreach ($earthquakes as $quake) {
                    if ($quake['magnitude'] >= 6.5 && $quake['depth'] < 70) {
                        return [
                            'active' => true,
                            'level' => $quake['magnitude'] >= 7.5 ? 'critical' : 'moderate',
                            'message' => 'Tsunami possible. Monitor coastal areas.',
                            'earthquake' => $quake,
                            'issued_at' => now(),
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::error('PhiVolcs Tsunami API Error: ' . $e->getMessage());
            }

            return [
                'active' => false,
                'level' => 'normal',
                'message' => 'No tsunami threat at this time.',
                'issued_at' => now(),
            ];
        });
    }

    /**
     * Calculate distance between two coordinates (Haversine formula)
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distance = $earthRadius * $c;

        return $distance;
    }

    /**
     * Get earthquake preparedness info
     */
    public function getPreparednessInfo()
    {
        return [
            'before' => [
                'Secure heavy furniture and appliances',
                'Prepare emergency kit with food, water, first aid',
                'Identify safe spots in each room',
                'Practice earthquake drills',
            ],
            'during' => [
                'DROP, COVER, and HOLD ON',
                'Stay away from windows and heavy objects',
                'If outdoors, move away from buildings',
                'If driving, pull over and stop',
            ],
            'after' => [
                'Check for injuries and damage',
                'Be prepared for aftershocks',
                'Stay away from damaged buildings',
                'Listen to emergency broadcasts',
            ],
        ];
    }
}
