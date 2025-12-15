<?php

namespace App\Services;

use App\Models\DisasterPrediction;
use App\Services\GeoService;

class DisasterPredictionService
{
    protected $geoService;

    public function __construct(GeoService $geoService)
    {
        $this->geoService = $geoService;
    }

    /**
     * 
     */
    public function getNearbyPredictions($latitude, $longitude, $radiusKm = 10)
    {
        return DisasterPrediction::get()->filter(function($prediction) use ($latitude, $longitude, $radiusKm) {
            if (!$prediction->latitude || !$prediction->longitude) return false;
            return $this->geoService->calculateDistance(
                $latitude, $longitude,
                $prediction->latitude, $prediction->longitude
            ) <= $radiusKm;
        });
    }

    /**
     *
     */
    public function analyzeRisk($nearbyPredictions)
    {
        $analysis = [
            'flood_risk' => 0,
            'landslide_risk' => 0,
            'earthquake_risk' => 0,
            'typhoon_risk' => 0,
            'estimated_recovery_days' => 0,
        ];

        foreach ($nearbyPredictions as $prediction) {
            $key = $prediction->disaster_type . '_risk';
            if (isset($analysis[$key])) {
                $analysis[$key] = max($analysis[$key], $prediction->risk_level ?? 0);
            }
            if ($prediction->predicted_recovery_days) {
                $analysis['estimated_recovery_days'] = max(
                    $analysis['estimated_recovery_days'],
                    $prediction->predicted_recovery_days
                );
            }
        }

        return $analysis;
    }
}
