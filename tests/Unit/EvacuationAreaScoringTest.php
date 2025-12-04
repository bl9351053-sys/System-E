<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\EvacuationArea;
use Illuminate\Support\Collection;

class EvacuationAreaScoringTest extends TestCase
{
    public function testHazardScoreWeightedAverage()
    {
        $area = new EvacuationArea();
        $area->latitude = 14.5995;
        $area->longitude = 120.9842;

        // two predictions at same position with risk levels 8 and 6 -> avg 7
        $predictions = collect([
            (object) ['latitude' => 14.5995, 'longitude' => 120.9842, 'risk_level' => 8],
            (object) ['latitude' => 14.5995, 'longitude' => 120.9842, 'risk_level' => 6],
        ]);

        $hazard = $area->computeHazardScore($predictions);
        $this->assertEquals(7.0, $hazard);
    }

    public function testHazardIgnoresDistantPredictions()
    {
        $area = new EvacuationArea();
        $area->latitude = 14.5995;
        $area->longitude = 120.9842;

        // far away prediction should be ignored
        $predictions = collect([
            (object) ['latitude' => 0.0, 'longitude' => 0.0, 'risk_level' => 10],
        ]);

        $hazard = $area->computeHazardScore($predictions, 5.0); // small maxDistance
        $this->assertEquals(0.0, $hazard);
    }

    public function testCalculatePrescriptiveScoreAdjustedByHazard()
    {
        $area = new EvacuationArea();
        $area->latitude = 14.5995;
        $area->longitude = 120.9842;
        $area->capacity = 100;
        $area->current_occupancy = 10;

        $predictionsLow = collect([(object) ['latitude' => 14.5995, 'longitude' => 120.9842, 'risk_level' => 1]]);
        $predictionsHigh = collect([(object) ['latitude' => 14.5995, 'longitude' => 120.9842, 'risk_level' => 9]]);

        $scoreLow = $area->calculatePrescriptiveScore(null, null, $predictionsLow);
        $scoreHigh = $area->calculatePrescriptiveScore(null, null, $predictionsHigh);

        $this->assertTrue($scoreLow > $scoreHigh, 'Score should be higher when hazards are lower');
    }
}
