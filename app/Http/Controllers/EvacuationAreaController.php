<?php

namespace App\Http\Controllers;

use App\Models\EvacuationArea;
use App\Services\PrescriptiveEngine;
use Illuminate\Support\Facades\DB;
use App\Models\Family;
use App\Models\DisasterPrediction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EvacuationAreaController extends Controller
{
  
    public function index(Request $request, PrescriptiveEngine $prescriptive)
{
    $evacuationAreas = EvacuationArea::with('families')->get();
    $disasterPredictions = DisasterPrediction::orderBy('risk_level', 'desc')
        ->orderBy('predicted_at', 'desc')
        ->get();

    $predicted_evacuees = 29.41; 

    foreach ($evacuationAreas as $area) {
        $remaining = $area->capacity - $area->current_occupancy;

        $area->distance_score = 7;

        $area->capacity_score = $remaining >= 50 ? 10 :
                                ($remaining >= 20 ? 7 : 5);

        $area->risk_score = $area->computeHazardScore($disasterPredictions);
        $area->final_score = $area->calculatePrescriptiveScore(null, null, $disasterPredictions);
    }

    $prescriptiveResult = $prescriptive->compute($request->input('latitude'), $request->input('longitude'), $predicted_evacuees);
        

    return view('evacuation-areas.index', [
        'evacuationAreas' => $evacuationAreas,
        'disasterPredictions' => $disasterPredictions,
        'predicted_evacuees' => $predicted_evacuees,
            'prescriptive' => $prescriptiveResult
    ]);
}



 
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'capacity' => 'required|integer|min:1',
            'facilities' => 'nullable|string',
            'contact_number' => 'nullable|string',
        ]);

        $evacuationArea = EvacuationArea::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Evacuation area added successfully!',
            'evacuation_area' => $evacuationArea
        ]);
    }

    public function show(EvacuationArea $evacuation_area)
{
    return view('evacuation-areas.show', [
        'evacuationArea' => $evacuation_area
    ]);
}

public function edit(EvacuationArea $evacuation_area)
{
    
}


    public function update(Request $request, EvacuationArea $evacuationArea)
    {
        
    }

    public function destroy(EvacuationArea $evacuationArea)
    {
       
    }

 
  public function go(Request $request, EvacuationArea $evacuationArea)
    {

        Log::info('EvacuationAreaController@go request', [
            'evacuation_area_id' => $evacuationArea->id,
            'payload' => $request->all(),
            'ip' => $request->ip(),
        ]);

        $validator = Validator::make($request->all(), [
            'family_head_name' => 'required|string|max:255',
            'total_members' => 'required|integer|min:1',
            'contact_number' => 'required|string|max:50',
            'address' => 'nullable|string|max:255',
            'special_needs' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            Log::warning('EvacuationAreaController@go validation failed', [
                'errors' => $validator->errors()->toArray(),
                'payload' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        $totalMembers = (int)$validated['total_members'];

    
        if (($evacuationArea->current_occupancy ?? 0) + $totalMembers > ($evacuationArea->capacity ?? 0)) {
            Log::info('EvacuationAreaController@go not enough space', [
                'current_occupancy' => $evacuationArea->current_occupancy,
                'capacity' => $evacuationArea->capacity,
                'requested' => $totalMembers,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Not enough space in this evacuation area.'
            ], 400);
        }

        try {
                
                DB::beginTransaction();
                $lockedArea = EvacuationArea::where('id', $evacuationArea->id)->lockForUpdate()->first();

                if (($lockedArea->current_occupancy ?? 0) + $totalMembers > ($lockedArea->capacity ?? 0)) {
                    DB::rollBack();
                    Log::info('EvacuationAreaController@go not enough space (after lock)', [
                        'current_occupancy' => $lockedArea->current_occupancy,
                        'capacity' => $lockedArea->capacity,
                        'requested' => $totalMembers,
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Not enough space in this evacuation area (race-checked).'
                    ], 400);
                }

                $family = Family::create([
                'evacuation_area_id' => $evacuationArea->id,
                'family_head_name' => $validated['family_head_name'],
                'total_members' => $totalMembers,
                'contact_number' => $validated['contact_number'],
                'address' => $validated['address'] ?? null,
                'special_needs' => $validated['special_needs'] ?? null,
                'checked_in_at' => now(),
                'status' => 'pending',
            ]);

                $lockedArea->current_occupancy = ($lockedArea->current_occupancy ?? 0) + $totalMembers;
                $lockedArea->status = $lockedArea->current_occupancy >= $lockedArea->capacity ? 'full' : 'available';
                $lockedArea->save();

                DB::commit();

               
                try {
                    event(new \App\Events\FamilyRegistered($family));
                } catch (\Exception $e) {
                    Log::warning('Failed to broadcast FamilyRegistered event: ' . $e->getMessage());
                }

            Log::info('EvacuationAreaController@go success', [
                'family_id' => $family->id,
                'evacuation_area_id' => $evacuationArea->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Family successfully registered!',
                'family' => $family
            ]);


        } catch (\Exception $e) {
            Log::error('EvacuationAreaController@go exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server error while registering family.'
            ], 500);
        }
    }



    public function nearest(Request $request)
    {
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');
        $predictions = DisasterPrediction::where('risk_level', '>=', 5)->orderBy('risk_level', 'desc')->get();
        $evacuationAreas = EvacuationArea::where('status', '!=', 'closed')
            ->get()
            ->map(function($area) use ($latitude, $longitude, $predictions) {
                $area->distance = $this->calculateDistance($latitude, $longitude, $area->latitude, $area->longitude);
                $area->score = $area->calculatePrescriptiveScore($latitude, $longitude, $predictions);
                return $area;
            })
            ->sortByDesc('score')
            ->values();

        return response()->json($evacuationAreas);
    }


    public function recommend(Request $request)
    {
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');

    
        $predicted = 29.41; 

        $predictions = DisasterPrediction::where('risk_level', '>=', 5)
                        ->orderBy('risk_level', 'desc')
                        ->get();

        $areas = EvacuationArea::where('status', '!=', 'closed')
                ->get()
                ->map(function($area) use ($latitude, $longitude, $predictions, $predicted) {

                    $remaining = $area->capacity - $area->current_occupancy;

                   
                    if ($remaining < $predicted) {
                        $area->final_score = 0;
                        return $area;
                    }

                    
                    $distance = $this->calculateDistance($latitude, $longitude, $area->latitude, $area->longitude);
                    $area->distance_score = $distance <= 5 ? 10 : ($distance <= 10 ? 7 : 3);
                    $area->capacity_score = $remaining >= 50 ? 10 : ($remaining >= 20 ? 7 : 5);

                    $area->risk_score = $area->computeHazardScore($predictions);

                    $area->final_score = $area->calculatePrescriptiveScore($latitude, $longitude, $predictions);

                    return $area;
                })
                ->sortByDesc('final_score')
                ->values();

        return response()->json([
            'recommended_area' => $areas->first(),
            'ranked_areas' => $areas               
        ]);
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
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

}
