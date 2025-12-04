<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\PrescriptiveEngine;

class PrescriptiveEngineOutputTest extends TestCase
{
    public function test_compute_returns_expected_fields()
    {
        $engine = new PrescriptiveEngine();
        $res = $engine->compute(null, null, 10);
        $this->assertIsArray($res);
        $this->assertArrayHasKey('predicted_total', $res);
        $this->assertArrayHasKey('recommended', $res);
        $this->assertArrayHasKey('allocations', $res);
        $this->assertArrayHasKey('recommended_by_allocation', $res);
        $this->assertArrayHasKey('recommended_safe', $res);
        $this->assertArrayHasKey('recommended_can_accommodate', $res);
    }
}
