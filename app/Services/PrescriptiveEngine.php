<?php

namespace App\Services;

use App\Models\EvacuationArea;
use App\Models\DisasterPrediction;
use Illuminate\Support\Facades\Log;

class PrescriptiveEngine
{
    /**
     * Compute prescriptive recommendations and allocations.
     *
     * @param float|null $latitude
     * @param float|null $longitude
     * @param int|float $predicted_total
     * @return array
     */
    public function compute(?float $latitude, ?float $longitude, $predicted_total = 0)
    {
        // Fetch live data
        $predictions = DisasterPrediction::orderBy('risk_level', 'desc')->get();

        // Load evacuation areas
        $areas = EvacuationArea::where('status', '!=', 'closed')->get();

        // Simple scoring and calculation used here — can be replaced by advanced models
        foreach ($areas as $area) {
            $remaining = $area->capacity - $area->current_occupancy;

            // distanceScore - 10 to 0 scale where closer is better
            if ($latitude && $longitude) {
                $distance = $this->calculateDistance($latitude, $longitude, $area->latitude, $area->longitude);
                $area->distance_score = $distance <= 5 ? 10 : ($distance <= 10 ? 7 : 3);
            } else {
                $area->distance_score = 7;
            }

            $area->capacity_score = $remaining >= 50 ? 10 : ($remaining >= 20 ? 7 : 5);

                // Compute hazard score via model helper
                $area->risk_score = $area->computeHazardScore($predictions);

            // Final score via model helper (ensures consistent weights & conversion from hazard to safety)
            $area->final_score = $area->calculatePrescriptiveScore($latitude, $longitude, $predictions);
        }

        // Rank areas by final score
        $ranked = $areas->sortByDesc('final_score')->values();

        // Greedy allocation for predicted total
        $allocations = [];
        $remainingToAllocate = intval(round($predicted_total));

        foreach ($ranked as $area) {
            if ($remainingToAllocate <= 0) break;
            $available = max(0, $area->capacity - $area->current_occupancy);
            if ($available <= 0) continue;
            // Apply a soft safety penalty to available capacity based on hazard
            // hazard (risk_score) is 0..10 where 10 is most dangerous
            $hazard = isset($area->risk_score) ? (float) $area->risk_score : 0.0;
            $safetyFactor = max(0.0, (10.0 - $hazard) / 10.0); // 0..1
            $effectiveAvailable = (int) floor($available * $safetyFactor);

            // If effectiveAvailable is zero, we still may want to allocate a small amount
            // if the area is very high-ranked; here we respect the safetyFactor and skip if none.
            if ($effectiveAvailable <= 0) {
                continue;
            }

            $assign = min($effectiveAvailable, $remainingToAllocate);
            $allocDistance = ($latitude && $longitude) ? $this->calculateDistance($latitude, $longitude, $area->latitude, $area->longitude) : null;
            $allocations[] = [
                'evacuation_area_id' => $area->id,
                'name' => $area->name,
                'assigned' => $assign,
                'capacity_left' => $available - $assign,
                'distance' => $allocDistance,
                'final_score' => $area->final_score,
                'hazard' => $hazard,
                'safety_factor' => $safetyFactor,
            ];
            $remainingToAllocate -= $assign;
        }

        // Fallback: if we still have unallocated evacuees, allocate to remaining available capacity regardless of hazard
        if ($remainingToAllocate > 0) {
            foreach ($ranked as $area) {
                if ($remainingToAllocate <= 0) break;
                $available = max(0, $area->capacity - $area->current_occupancy);
                // determine how much we already allocated to this area
                $already = 0;
                foreach ($allocations as $alloc) {
                    if ($alloc['evacuation_area_id'] == $area->id) {
                        $already = $alloc['assigned'];
                        break;
                    }
                }
                $remainingAvail = max(0, $available - $already);
                if ($remainingAvail <= 0) continue;
                $assign = min($remainingAvail, $remainingToAllocate);
                // if already present, increment the assigned and capacity_left
                $foundKey = null;
                foreach ($allocations as $k => $alloc) {
                    if ($alloc['evacuation_area_id'] == $area->id) { $foundKey = $k; break; }
                }
                if ($foundKey !== null) {
                    $allocations[$foundKey]['assigned'] += $assign;
                    $allocations[$foundKey]['capacity_left'] = max(0, $available - $allocations[$foundKey]['assigned']);
                } else {
                    $allocations[] = [
                        'evacuation_area_id' => $area->id,
                        'name' => $area->name,
                        'assigned' => $assign,
                        'capacity_left' => $available - $assign,
                            'distance' => ($latitude && $longitude) ? $this->calculateDistance($latitude, $longitude, $area->latitude, $area->longitude) : null,
                        'final_score' => $area->final_score,
                        'hazard' => $area->risk_score,
                        'safety_factor' => max(0.0, (10.0 - $area->risk_score) / 10.0),
                    ];
                }
                $remainingToAllocate -= $assign;
            }
        }

        $recommended = $ranked->first();

        // Determine whether the recommended area can accommodate all evacuees on its own
        $recommended_can_accommodate = false;
        $recommended_effective_capacity = 0;
        if ($recommended) {
            $recAvailable = max(0, $recommended->capacity - $recommended->current_occupancy);
            $recHazard = isset($recommended->risk_score) ? (float) $recommended->risk_score : 0.0;
            $recSafetyFactor = max(0.0, (10.0 - $recHazard) / 10.0);
            $recommended_effective_capacity = (int) floor($recAvailable * $recSafetyFactor);
            $recommended_can_accommodate = $recommended_effective_capacity >= intval(round($predicted_total));
        }

        // total assigned (sum of allocations)
        $totalAssigned = array_sum(array_map(fn($a) => $a['assigned'], $allocations));

        // recommended by allocation: area with the most assigned
        $recommended_by_allocation = null;
        if (!empty($allocations)) {
            $topAlloc = collect($allocations)->sortByDesc('assigned')->first();
            $recommended_by_allocation = $ranked->firstWhere('id', $topAlloc['evacuation_area_id']);
        }

        // recommended safe: highest-scoring area with hazard <= 6 (configurable later)
        $safeThreshold = 6; // can be extracted to config
        $recommended_safe = $ranked->firstWhere('risk_score', '<=', $safeThreshold);

        return [
            'predicted_total' => $predicted_total,
            'recommended' => $recommended,
            'recommended_by_allocation' => $recommended_by_allocation,
            'recommended_safe' => $recommended_safe,
            'total_assigned' => $totalAssigned,
            'recommended_can_accommodate' => $recommended_can_accommodate,
            'recommended_effective_capacity' => $recommended_effective_capacity,
            'allocations' => $allocations,
            'unallocated' => $remainingToAllocate
        ];
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return round($earthRadius * $c, 2);
    }
}
