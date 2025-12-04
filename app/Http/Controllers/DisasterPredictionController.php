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
    // Admin / Resident index
    public function index()
    {
        $predictions = DisasterPrediction::orderByDesc('risk_level')
            ->orderByDesc('predicted_at')
            ->paginate(20);

        return view('disaster-predictions.index', compact('predictions'));
    }

    // Admin create form
    public function create()
    {
        return view('disaster-predictions.create');
    }

    // Admin store prediction
    public function store(Request $request)
    {
         $validated = $request->validate([
        'disaster_type' => 'required|string',
        'description' => 'nullable|string', // admin
        'severity' => 'nullable|in:low,medium,high,critical', // admin
        'affected_areas' => 'nullable|string', // admin
        'probability' => 'nullable|numeric|min:0|max:100', // admin
        'latitude' => 'nullable|numeric', // resident
        'longitude' => 'nullable|numeric', // resident
        'location_name' => 'nullable|string', // resident
        'risk_level' => 'nullable|integer|min:1|max:10', // resident
        'predicted_recovery_days' => 'nullable|integer|min:0', // resident
        'predicted_date' => 'nullable|date', // admin
        'valid_until' => 'nullable|date', // admin
        'predicted_at' => 'nullable|date', // resident
        'user_id' => 'nullable|exists:users,id', // admin
    ]);

        DisasterPrediction::create($validated);

        return redirect()->route('disaster-predictions.index')
            ->with('success', 'Disaster prediction added successfully!');
    }

    // Show a single prediction
    public function show(DisasterPrediction $disasterPrediction)
    {
        return view('disaster-predictions.show', compact('disasterPrediction'));
    }

    // API: get active high-risk predictions
    public function active()
    {
        $predictions = DisasterPrediction::where('risk_level', '>=', 5)
            ->orderByDesc('risk_level')
            ->get();

        return response()->json($predictions);
    }

    // Analyze risk near a location
    public function analyze(Request $request)
    {
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');

        if (!$latitude || !$longitude) {
            return response()->json(['error' => 'Latitude and longitude required'], 422);
        }

        // Filter predictions within 10km
        // Use DisasterPredictionService for nearby predictions and risk analysis
        $nearbyPredictions = $this->disasterPredictionService->getNearbyPredictions($latitude, $longitude, 10);
        $analysis = $this->disasterPredictionService->analyzeRisk($nearbyPredictions);

        return response()->json($analysis);
    }

    // Distance calculation now handled by GeoService
}
