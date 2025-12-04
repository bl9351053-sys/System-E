<?php

namespace App\Http\Controllers;

use App\Services\PrescriptiveEngine;
use App\Models\DisasterPrediction;
use Illuminate\Http\Request;

class PrescriptiveController extends Controller
{
    protected $engine;

    public function __construct(PrescriptiveEngine $engine)
    {
        $this->engine = $engine;
    }

    public function recommend(Request $request)
    {
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');
        $predicted = $request->input('predicted', 0);

        if (!$predicted) {
            $p = DisasterPrediction::orderBy('predicted_at', 'desc')->first();
            $predicted = $p?->predicted_evacuees ?? 0;
        }

        $result = $this->engine->compute($latitude, $longitude, $predicted);

        return response()->json($result);
    }
}
