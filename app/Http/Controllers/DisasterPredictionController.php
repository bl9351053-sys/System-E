<?php

namespace App\Http\Controllers;

use App\Models\DisasterPrediction;
use Illuminate\Http\Request;
use App\Services\GeoService;
use App\Services\DisasterPredictionService;

class DisasterPredictionController extends Controller
{
    protected $geoService;
    protected $disasterPredictionService;

    public function __construct(GeoService $geoService, DisasterPredictionService $disasterPredictionService)
    {
        $this->geoService = $geoService;
        $this->disasterPredictionService = $disasterPredictionService;
    }
    public function index()
    {
        $predictions = DisasterPrediction::orderByDesc('risk_level')
            ->orderByDesc('predicted_at')
            ->paginate(20);

        if (request()->expectsJson()) {
            return response()->json($predictions);
        }

        return view('disaster-predictions.index', compact('predictions'));
    }

    public function create()
    {
        return view('disaster-predictions.create');
    }

    public function store(Request $request)
    {
         $validated = $request->validate([
        'disaster_type' => 'required|string',
        'description' => 'nullable|string',
        'severity' => 'nullable|in:low,medium,high,critical',
        'affected_areas' => 'nullable|string',
        'probability' => 'nullable|numeric|min:0|max:100',
        'latitude' => 'nullable|numeric',
        'longitude' => 'nullable|numeric',
        'location_name' => 'nullable|string',
        'risk_level' => 'nullable|integer|min:1|max:10',
        'predicted_recovery_days' => 'nullable|integer|min:0',
        'predicted_date' => 'nullable|date',
        'valid_until' => 'nullable|date',
        'predicted_at' => 'nullable|date',
        'user_id' => 'nullable|exists:users,id',
    ]);

        DisasterPrediction::create($validated);

        return redirect()->route('disaster-predictions.index')
            ->with('success', 'Disaster prediction added successfully!');
    }

    public function show(DisasterPrediction $disasterPrediction)
    {
        if (request()->expectsJson()) {
            return response()->json(['data' => $disasterPrediction]);
        }

        return view('disaster-predictions.show', compact('disasterPrediction'));
    }

    public function active()
    {
        $predictions = DisasterPrediction::where('risk_level', '>=', 5)
            ->orderByDesc('risk_level')
            ->get();

        return response()->json($predictions);
    }

    public function analyze(Request $request)
    {
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');

        if (!$latitude || !$longitude) {
            return response()->json(['error' => 'Latitude and longitude required'], 422);
        }


        $nearbyPredictions = $this->disasterPredictionService->getNearbyPredictions($latitude, $longitude, 10);
        $analysis = $this->disasterPredictionService->analyzeRisk($nearbyPredictions);

        return response()->json($analysis);
    }

}
