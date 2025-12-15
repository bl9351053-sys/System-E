<?php

namespace App\Services;

use App\Models\EvacuationArea;

class AllocationService
{
    /**
     * 
     *
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     * @return float
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return round($earthRadius * $c, 2);
    }

    /**
     * 
     * 
     *
     * @param EvacuationArea $area
     * @param float $latitude
     * @param float $longitude
     * @param \Illuminate\Support\Collection|array $activePredictions
     * @return EvacuationArea
     */
    public function scoreArea(EvacuationArea $area, float $latitude, float $longitude, $activePredictions): EvacuationArea
    {
        $distance = $this->calculateDistance($latitude, $longitude, $area->latitude, $area->longitude);
        $area->distance_score = $distance <= 5 ? 10 : ($distance <= 10 ? 7 : 3);

        $remaining = $area->capacity - $area->current_occupancy;
        $area->capacity_score = $remaining >= 50 ? 10 : ($remaining >= 20 ? 7 : 3);

        $area->risk_score = method_exists($area, 'computeHazardScore') ? $area->computeHazardScore($activePredictions) : 0;
        $area->final_score = method_exists($area, 'calculatePrescriptiveScore') ? $area->calculatePrescriptiveScore($latitude, $longitude, $activePredictions) : (($area->distance_score + $area->capacity_score + $area->risk_score) / 3);

        return $area;
    }

    /**
     * 
     *
     * @param float $latitude
     * @param float $longitude
     * @param \Illuminate\Support\Collection|array $activePredictions
     * @return \Illuminate\Support\Collection
     */
    public function recommendAreas(float $latitude, float $longitude, $activePredictions)
    {
        return EvacuationArea::where('status', '!=', 'closed')
            ->get()
            ->map(function ($area) use ($latitude, $longitude, $activePredictions) {
                return $this->scoreArea($area, $latitude, $longitude, $activePredictions);
            })
            ->sortByDesc('final_score')
            ->values();
    }

    /**
     * Allocate forecasted evacuees across recommended areas.
     * Returns allocation array with top area and alternates similar to previous controller logic.
     *
     * @param \Illuminate\Support\Collection|array $recommendedAreas
     * @param int $forecastedEvacuees
     * @return array 
     */
    public function allocate($recommendedAreas, int $forecastedEvacuees): array
    {
        $remainingEvacuees = $forecastedEvacuees;
        $allocations = [];

        foreach ($recommendedAreas as $area) {
            $availableSlots = $area->capacity - $area->current_occupancy;
            if ($availableSlots <= 0) {
                continue;
            }

            if ($remainingEvacuees <= $availableSlots) {
                $allocations[] = ['area' => $area, 'allocated' => $remainingEvacuees];
                $remainingEvacuees = 0;
                break;
            }

            $allocations[] = ['area' => $area, 'allocated' => $availableSlots];
            $remainingEvacuees -= $availableSlots;
        }

        $topRecommendedArea = null;
        $topAllocated = 0;
        $alternateAreas = [];

        if (count($allocations) > 0) {
            $topRecommendedArea = $allocations[0]['area'];
            $topAllocated = $allocations[0]['allocated'];
            $alternateAreas = array_slice($allocations, 1);
        }

        return [
            'allocations' => $allocations,
            'topRecommendedArea' => $topRecommendedArea,
            'topAllocated' => $topAllocated,
            'alternateAreas' => $alternateAreas,
        ];
    }
}
