<?php

namespace App\Http\Controllers;

use App\Models\Family;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FamilyController extends Controller
{
    public function index(Request $request)
    {
        $families = Family::with('evacuationArea')
            ->whereNull('checked_out_at')
            ->orderBy('checked_in_at', 'desc')
            ->paginate(15);

        if ($request->wantsJson()) {
            return response()->json(['families' => $families]);
        }

        return view('families.index', compact('families'));
    }

    public function show(Family $family)
    {
        return view('families.show', compact('family'));
    }

    public function edit(Family $family)
    {
        $evacuationAreas = \App\Models\EvacuationArea::all();
        return view('families.edit', compact('family', 'evacuationAreas'));
    }

    public function update(Request $request, Family $family)
    {
        $validated = $request->validate([
            'family_head_name' => 'required|string|max:255',
            'total_members' => 'required|integer|min:1',
            'contact_number' => 'required|string|max:50',
            'address' => 'nullable|string|max:255',
            'special_needs' => 'nullable|string|max:255',
            'evacuation_area_id' => 'required|exists:evacuation_areas,id',
        ]);

        $oldEvacuationAreaId = $family->evacuation_area_id;
        DB::transaction(function () use ($family, $validated, $oldEvacuationAreaId) {
            $family->update($validated);

            if ($oldEvacuationAreaId != $validated['evacuation_area_id']) {
            $oldArea = \App\Models\EvacuationArea::find($oldEvacuationAreaId);
            if ($oldArea) {
                $oldArea->current_occupancy -= $family->total_members;
                $oldArea->updateStatus();
                $oldArea->save();
            }

            $newArea = \App\Models\EvacuationArea::find($validated['evacuation_area_id']);
            if ($newArea) {
                $newArea->current_occupancy += $family->total_members;
                $newArea->updateStatus();
                $newArea->save();
            }
            }
        });

        return redirect()->route('families.show', $family)->with('success', 'Family updated successfully!');
    }

    public function checkout(Family $family)
    {
        DB::transaction(function () use ($family) {
            $family->checked_out_at = now();
            $family->status = 'returned';
            $family->save();

            $evacuationArea = $family->evacuationArea;
            $evacuationArea->current_occupancy -= $family->total_members;
            $evacuationArea->status = $evacuationArea->current_occupancy >= $evacuationArea->capacity ? 'full' : 'available';
            $evacuationArea->save();
        });

        return redirect()->back()->with('success', 'Family checked out successfully!');
    }
}
