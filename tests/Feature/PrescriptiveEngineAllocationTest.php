<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\EvacuationArea;
use App\Models\DisasterPrediction;
use App\Services\PrescriptiveEngine;

class PrescriptiveEngineAllocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_soft_risk_penalty_reduces_allocation_to_high_hazard_areas()
    {
        // Create two areas: one safe and one risky
        $safe = EvacuationArea::create([
            'name' => 'Safe Hall',
            'address' => 'Safe St',
            'latitude' => 14.6000,
            'longitude' => 120.9850,
            'capacity' => 100,
            'current_occupancy' => 0,
            'status' => 'available'
        ]);

        $risky = EvacuationArea::create([
            'name' => 'Risky Field',
            'address' => 'Risk Rd',
            'latitude' => 14.6005,
            'longitude' => 120.9855,
            'capacity' => 100,
            'current_occupancy' => 0,
            'status' => 'available'
        ]);

        // Create a high risk prediction near the risky area
        DisasterPrediction::create([
            'location_name' => 'Fault Point',
            'latitude' => 14.6004,
            'longitude' => 120.9854,
            'risk_level' => 9,
            'disaster_type' => 'earthquake',
            'predicted_at' => now(),
        ]);

        $predicted_total = 50;
        $engine = new PrescriptiveEngine();
        $result = $engine->compute(null, null, $predicted_total);

        $this->assertArrayHasKey('allocations', $result);
        $allocations = collect($result['allocations']);

        // Find assignments for our two areas
        $safeAlloc = $allocations->firstWhere('evacuation_area_id', $safe->id);
        $riskyAlloc = $allocations->firstWhere('evacuation_area_id', $risky->id);

        $this->assertNotNull($safeAlloc, 'Safe area should be present in allocations');
        // Risky area allocation should be <= safe area allocation due to penalty
        if ($riskyAlloc) {
            $this->assertTrue($safeAlloc['assigned'] >= $riskyAlloc['assigned']);
        }
    }
}
