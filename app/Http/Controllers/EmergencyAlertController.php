<?php

namespace App\Http\Controllers;

use App\Models\EmergencyAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmergencyAlertController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $alerts = EmergencyAlert::orderBy('created_at', 'desc')->get();
        return response()->json(['alerts' => $alerts]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'severity' => 'required|in:low,medium,high',
            'status' => 'required|in:active,inactive',
            'area' => 'required|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $alert = EmergencyAlert::create($request->all());
        return response()->json(['alert' => $alert], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(EmergencyAlert $emergencyAlert)
    {
        return response()->json(['alert' => $emergencyAlert]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmergencyAlert $emergencyAlert)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'severity' => 'required|in:low,medium,high',
            'status' => 'required|in:active,inactive',
            'area' => 'required|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $emergencyAlert->update($request->all());
        return response()->json(['alert' => $emergencyAlert]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmergencyAlert $emergencyAlert)
    {
        $emergencyAlert->delete();
        return response()->json(['message' => 'Emergency alert deleted successfully']);
    }
}
