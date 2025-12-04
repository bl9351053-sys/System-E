<?php

namespace App\Http\Controllers;

use App\Models\DisasterUpdate;
use Illuminate\Http\Request;

class DisasterUpdateController extends Controller
{
    public function index()
    { 
        $updates = DisasterUpdate::orderBy('issued_at', 'desc')->paginate(20);
        return view('disaster-updates.index', compact('updates'));
    }

    public function create()
    {
        return view('disaster-updates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'disaster_type' => 'required|in:typhoon,earthquake,flood,landslide',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'severity' => 'required|in:low,moderate,high,critical',
            'source' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'issued_at' => 'required|date',
        ]);

        $validated['source'] = $validated['source'] ?? 'PAGASA/PhiVolcs';

        DisasterUpdate::create($validated);

        return redirect()->route('disaster-updates.index')
            ->with('success', 'Disaster update added successfully!');
    }

    public function show(DisasterUpdate $disasterUpdate)
    {
        return view('disaster-updates.show', compact('disasterUpdate'));
    }

    public function latest()
    {
        $updates = DisasterUpdate::orderBy('issued_at', 'desc')->take(10)->get();
        return response()->json($updates);
    }
}
