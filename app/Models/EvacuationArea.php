<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvacuationArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'latitude',
        'longitude',
        'capacity',
        'current_occupancy',
        'status',
        'facilities',
        'contact_number',
    ];

    public function families()
    {
        return $this->hasMany(Family::class);
    }

   public function updateStatus() {
    if($this->current_occupancy >= $this->capacity){
        $this->status = 'full';
    } elseif($this->current_occupancy == 0){
        $this->status = 'available';
    } else {
        $this->status = 'available';
    }
    $this->save(); 
}


    public function getAvailableSpaceAttribute()
    {
        return $this->capacity - $this->current_occupancy;
    }

    public function getOccupancyPercentageAttribute()
    {
        return $this->capacity > 0 ? round(($this->current_occupancy / $this->capacity) * 100, 2) : 0;
    }

    public function calculatePrescriptiveScore($userLatitude, $userLongitude, $riskLevel = 0)
{
    // Distance score (closer = higher score)
    if (is_null($userLatitude) || is_null($userLongitude) || !is_numeric($userLatitude) || !is_numeric($userLongitude)) {
        // neutral distance score if no user location is provided
        $distanceScore = 7;
    } else {
        $distance = $this->calculateDistance($userLatitude, $userLongitude, $this->latitude, $this->longitude);
        $distanceScore = max(0, 10 - $distance); // max 10 points
    }

    // Capacity score (more space = higher score)
    $capacityScore = ($this->capacity > 0) ? ($this->getAvailableSpaceAttribute() / $this->capacity) * 10 : 0;

    // If $riskLevel is a collection of predictions, compute hazard score
    $hazardScore = 0;
    if ($riskLevel instanceof \Illuminate\Support\Collection || is_array($riskLevel)) {
        $hazardScore = $this->computeHazardScore(collect($riskLevel));
    } else {
        $hazardScore = max(0, min(10, (float) $riskLevel));
    }

    // Risk score: lower hazard -> higher riskScore (safety contribution)
    $riskScore = max(0, 10 - $hazardScore);

    // Weighted total score (distance 40%, capacity 30%, risk 30%)
    $totalScore = ($distanceScore * 0.4) + ($capacityScore * 0.3) + ($riskScore * 0.3);

    return round($totalScore, 2);
}

/**
 * Compute hazard score using a distance-weighted average of nearby predictions.
 * Returns a value between 0 and 10 where 0 = no hazard and 10 = highest hazard.
 *
 * @param \Illuminate\Support\Collection $predictions
 * @param float $maxDistanceKm
 * @return float
 */
public function computeHazardScore($predictions, $maxDistanceKm = 10.0)
{
    $sumWeighted = 0.0;
    $sumWeights = 0.0;

    foreach ($predictions as $p) {
        if (!isset($p->latitude) || !isset($p->longitude) || !isset($p->risk_level)) continue;
        $d = $this->calculateDistance($this->latitude, $this->longitude, $p->latitude, $p->longitude);
        // weight decreases non-linearly with distance to emphasize nearer risks
        $linearWeight = max(0, 1 - ($d / $maxDistanceKm));
        $weight = pow($linearWeight, 2); // quadratic decay
        if ($weight <= 0) continue; // ignore distant predictions
        $sumWeighted += ((float) $p->risk_level) * $weight;
        $sumWeights += $weight;
    }

    if ($sumWeights <= 0) return 0.0;
    $hazard = $sumWeighted / $sumWeights; // weighted average in 0..10 range
    // Clamp to 0..10
    $hazard = max(0, min(10, $hazard));
    return round($hazard, 2);
}

// Utility function for distance
private function calculateDistance($lat1, $lon1, $lat2, $lon2)
{
    $earthRadius = 6371; // km
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $earthRadius * $c;
}

}
