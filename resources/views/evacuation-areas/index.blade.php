@extends('layouts.app')

@section('title', 'Evacuation Areas')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="card">
    <div class="card-header">🗺️ Evacuation Areas Map</div>
    
    <!-- Filter controls -->
    
    <div class="mb-1" style="display: flex; gap: 1rem; flex-wrap: wrap;">
        <label class="form-label" style="display: flex; align-items: center; gap: 0.5rem; margin: 0;">
            <input type="checkbox" id="showPredictions" checked>
            Show Disaster Predictions (Color-Coded by Severity)
        </label>
        <label class="form-label" style="display: flex; align-items: center; gap: 0.5rem; margin: 0;">
            <input type="checkbox" id="showFaultLines">
            Show Earthquake Fault Lines
        </label>
    </div>
    
    <div class="info-box warning mb-1" style="background: #fff3cd; padding: 0.75rem; border-radius: 8px; border-left: 4px solid #ffc107;">
        <strong>📊 Prediction Severity:</strong>
        <div style="display: flex; gap: 1rem; margin-top: 0.5rem; flex-wrap: wrap; font-size: 0.9rem;">
            <span style="display: flex; align-items: center; gap: 0.3rem;">
                <span style="width: 16px; height: 16px; background: #28a745; border-radius: 50%; display: inline-block; border: 2px solid white; box-shadow: 0 1px 3px rgba(0,0,0,0.3);"></span>
                <strong>Low (1-3)</strong>
            </span>
            <span style="display: flex; align-items: center; gap: 0.3rem;">
                <span style="width: 16px; height: 16px; background: #ffc107; border-radius: 50%; display: inline-block; border: 2px solid white; box-shadow: 0 1px 3px rgba(0,0,0,0.3);"></span>
                <strong>Moderate (4-5)</strong>
            </span>
            <span style="display: flex; align-items: center; gap: 0.3rem;">
                <span style="width: 16px; height: 16px; background: #fd7e14; border-radius: 50%; display: inline-block; border: 2px solid white; box-shadow: 0 1px 3px rgba(0,0,0,0.3);"></span>
                <strong>High (6-7)</strong>
            </span>
            <span style="display: flex; align-items: center; gap: 0.3rem;">
                <span style="width: 16px; height: 16px; background: #dc3545; border-radius: 50%; display: inline-block; border: 2px solid white; box-shadow: 0 1px 3px rgba(0,0,0,0.3);"></span>
                <strong>Critical (8-10)</strong>
            </span>
        </div>
    </div>
    
    <div id="map"></div>
    
    <div class="location-status-box">
        <h4 class="mb-05">📍 Your Location</h4>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <button id="getLocationBtn" class="btn btn-success">Get My Location</button>
            <button id="addMyLocationBtn" class="btn btn-primary" style="display: none;">➕ Add My Location as Evacuation Area</button>
        </div>
        <span id="locationStatus" class="text-muted" style="margin-left: 1rem; display: block; margin-top: 0.5rem;"></span>
    </div>
</div>


<!-- PRESCRIPTIVE ANALYTICS SECTION -->
<div class="p-6 mb-8">

  

    @php
        $topArea = $evacuationAreas->sortByDesc(fn($area) => $area->final_score ?? 0)->first();
    @endphp

    <!-- Combined Prescriptive Analytics Card -->
    <div id="prescriptiveCard" class="card bg-white rounded-2xl shadow-lg p-5 mb-6 card-shadow">
            <div class="flex items-center justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-4 flex-1 min-w-0">
                <div class="px-4 py-3 bg-blue-50 rounded-lg text-center" style="min-width:120px;">
                    <h2 class="text-3xl font-extrabold text-gray-800 mb-6 flex items-center gap-3">
        📊 Prescriptive Analytics
    </h2>
                    <div class="text-xs text-gray-500">Predicted Evacuees</div>
                    <div class="text-2xl font-bold text-gray-800">{{ $predicted_evacuees }}</div>
                    <!-- prediction sparkline removed per request -->
                </div>

                <div class="flex-1 min-w-0">
                    <div class="text-sm text-gray-500">Recommended Evacuation Area (by score)</div>
                    @if($topArea)
                        <div class="text-lg font-semibold text-green-700">{{ $topArea->name }} <span class="text-xs text-gray-600">- {{ $topArea->address }}</span></div>
                        <div class="mt-1 text-sm text-gray-600">Hazard: <span class="font-semibold">{{ $topArea->risk_score }}</span> &middot; Capacity left: <span class="font-semibold">{{ $topArea->capacity - $topArea->current_occupancy }}</span></div>
                        <div class="mt-1 text-sm text-gray-600">Score: <strong class="text-green-800">{{ number_format($topArea->final_score, 1) }}/10</strong></div>
                        @if(!empty($prescriptive['recommended_by_allocation']) && $prescriptive['recommended_by_allocation']->id !== $topArea->id)
                            <div class="mt-1 text-sm text-gray-600">Top allocation: <strong class="text-blue-700">{{ $prescriptive['recommended_by_allocation']->name }}</strong> — Assigned: <strong>{{ $prescriptive['total_assigned'] ?? 0 }}</strong></div>
                        @endif
                    @else
                        <div class="text-sm text-gray-500">No recommended area available</div>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button id="prescriptiveExpandBtn" aria-expanded="false" aria-controls="prescriptiveDetails" class="btn btn-outline px-4 py-2" title="Show prescriptive details">
                    <span class="prescriptive-label">Details</span>
                    <span id="prescriptiveDetailsCount" class="badge badge-small ml-2 px-2 py-1 bg-gray-100 text-sm rounded">0</span>
                    <span class="prescriptive-icon ml-2" aria-hidden="true">▾</span>
                </button>
            </div>
        </div>

        {{-- Prescriptive summary badges --}}
        <div class="mt-4 flex gap-3 items-center flex-wrap">
            
            <div class="px-3 py-2 bg-white rounded shadow-sm text-sm">
                <div class="text-xs text-gray-500">Total Assigned</div>
                <div class="text-lg font-bold">- {{ $prescriptive['total_assigned'] ?? 0 }}</div>
                @php
                    $pred = $prescriptive['predicted_total'] ?? $predicted_evacuees;
                    $assigned = $prescriptive['total_assigned'] ?? 0;
                    $pctTotal = ($pred > 0) ? round(($assigned / $pred) * 100) : 0;
                @endphp
                <div class="mt-2">
                    <div class="progress" style="height: 8px; background: #f1f5f9; border-radius: 8px; overflow: hidden;">
                        <div style="width: {{ $pctTotal }}%; background: #34d399; height: 8px; border-radius: 8px;"></div>
                    </div>
                    <div class="text-xs text-gray-500">{{ $assigned }} allocated of {{ $pred }} ({{ $pctTotal }}%)</div>
                </div>
            </div>
            <div class="px-3 py-2 bg-white rounded shadow-sm text-sm">
                <div class="text-xs text-gray-500">Unallocated</div>
                <div class="text-lg font-bold"> - {{ $prescriptive['unallocated'] ?? 0 }}</div>
            </div>
            @if(!empty($prescriptive['recommended_by_allocation']))
                <div class="px-3 py-2 bg-blue-50 rounded shadow-sm text-sm">
                    <div class="text-xs text-gray-500">Top Allocation</div>
                    <div class="text-lg font-semibold"> - {{ $prescriptive['recommended_by_allocation']->name }} </div>
                </div>
            @endif
            @if(!empty($prescriptive['recommended_safe']))
                <div class="px-3 py-2 bg-green-50 rounded shadow-sm text-sm">
                    <div class="text-xs text-gray-500">Recommended (Safe)</div>
                    <div class="text-lg font-semibold"> - {{ $prescriptive['recommended_safe']->name }}</div>
                </div>
            @endif
        </div>

        <div id="prescriptiveDetails" class="mt-4 prescriptive-panel" aria-hidden="true">
            <div class="mb-4">
                <div class="card p-3 bg-white shadow-sm rounded-lg card-shadow">
                    <div class="text-sm text-gray-600 mb-2">Suggested Allocation Plan</div>
                    <div class="mt-2 text-sm text-gray-500 mb-3">This plan attempts to balance safety and capacity. Areas with higher hazard are penalized in assigned capacity.</div>
                    @if(!empty($prescriptive))
                        @if($prescriptive['recommended_can_accommodate'])
                            <div class="text-sm text-gray-700 p-4 bg-green-50 border rounded">
                                ✅ The recommended evacuation area <strong>{{ $prescriptive['recommended']->name }}</strong> can accommodate all <strong>{{ $prescriptive['predicted_total'] }}</strong> predicted evacuees on its own (effective capacity: <strong>{{ $prescriptive['recommended_effective_capacity'] }}</strong>).
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="table w-full text-gray-700 table-custom table-custom-alloc">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th>Evacuation Area</th>
                                        <th>Assigned</th>
                                        <th>Capacity Left After</th>
                                        <th>Hazard</th>
                                        <th>Safety Factor</th>
                                        <th>Final Score</th>
                                    </tr>
                                </thead>
                                <tbody>
                                            @foreach($prescriptive['allocations'] as $a)
                                            @php
                                                $isTopAlloc = (!empty($prescriptive['recommended_by_allocation']) && $prescriptive['recommended_by_allocation']->id == ($a['evacuation_area_id'] ?? null));
                                            @endphp
                                            <tr class="{{ $isTopAlloc ? 'bg-blue-50' : '' }}">
                                        <td>{{ $a['name'] }}</td>
                                        <td>
                                            <div class="font-semibold">{{ $a['assigned'] }}</div>
                                            <div class="text-xs text-gray-500">Assigned</div>
                                        </td>
                                        <td>
                                            <div style="width: 150px;">
                                                <div class="progress" style="height: 8px; background: #f1f5f9; border-radius: 8px; overflow: hidden;">
                                                    @php
                                                        $capLeft = $a['capacity_left'] ?? 0;
                                                        $totalCap = ($evacuationAreas->firstWhere('id', $a['evacuation_area_id'])?->capacity ?? 0);
                                                        $assigned = $a['assigned'];
                                                        $pct = $totalCap > 0 ? round(($assigned / $totalCap) * 100) : 0;
                                                    @endphp
                                                    <div style="width: {{ $pct }}%; background: #667eea; height: 8px; border-radius: 8px;"></div>
                                                </div>
                                                <div class="text-xs text-gray-500">{{ $assigned }} / {{ $totalCap }} ({{ $pct }}%)</div>
                                            </div>
                                        </td>
                                                <td>
                                                    @php
                                                        $haz = $a['hazard'] ?? null;
                                                        $hazClass = 'hazard-low';
                                                        if ($haz === null) $hazClass = '';
                                                        elseif ($haz >= 8) $hazClass = 'hazard-critical';
                                                        elseif ($haz >= 7) $hazClass = 'hazard-high';
                                                        elseif ($haz >= 4) $hazClass = 'hazard-moderate';
                                                    @endphp
                                                    @if($haz !== null)
                                                        <span class="hazard-badge {{ $hazClass }}">{{ $haz }}</span>
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                        <td>{{ isset($a['safety_factor']) ? number_format($a['safety_factor'], 2) : '—' }}</td>
                                        <td>{{ number_format($a['final_score'], 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                </table>
                            </div>
                        @endif

                        @if(!empty($prescriptive['unallocated']) && $prescriptive['unallocated'] > 0)
                            <div class="mt-3 p-3 bg-yellow-100 rounded-md text-sm">
                                ⚠️ <strong>{{ $prescriptive['unallocated'] }}</strong> evacuees were not allocated due to capacity limits.
                            </div>
                        @endif
                    @else
                        <div class="text-sm text-gray-500">No plan computed yet.</div>
                    @endif
                </div>
            </div>

                <div id="rankingTableWrapper" class="card overflow-x-auto max-h-96 border rounded-lg shadow-sm p-4 card-shadow">
                <div class="text-sm text-gray-600 mb-2">Full Ranking</div>
                <table class="min-w-full border-collapse text-gray-700 table-custom table-custom-ranking">
                    <thead class="bg-gray-100 text-gray-700 sticky top-0">
                        <tr>
                            <th class="p-3 border-b text-left">Area</th>
                            <th class="p-3 border-b text-center">Capacity Left</th>
                            <th class="p-3 border-b text-center">Risk</th>
                            <th class="p-3 border-b text-center">Distance</th>
                            <th class="p-3 border-b text-center">Final Score</th>
                            <th class="p-3 border-b text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($evacuationAreas->sortByDesc('final_score') as $area)
                            <tr class="hover:bg-blue-50 transition">
                                <td class="p-2 border font-semibold">{{ $area->name }}</td>
                                <td class="p-2 border text-center">{{ $area->capacity - $area->current_occupancy }}</td>
                                <td class="p-2 border text-center">
                                    @php
                                        $haz = $area->risk_score ?? null;
                                        $hazClass = 'hazard-low';
                                        if ($haz === null) $hazClass = '';
                                        elseif ($haz >= 8) $hazClass = 'hazard-critical';
                                        elseif ($haz >= 7) $hazClass = 'hazard-high';
                                        elseif ($haz >= 4) $hazClass = 'hazard-moderate';
                                    @endphp
                                    @if($haz !== null)
                                        <span class="hazard-badge {{ $hazClass }}">{{ $haz }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="p-2 border text-center">{{ $area->distance_score }}</td>
                                <td class="p-2 border text-center font-semibold">{{ number_format($area->final_score, 2) }}</td>
                                <td class="p-2 border text-center">
                                    @php $remaining = $area->capacity - $area->current_occupancy; @endphp
                                    @if($remaining <= 0)
                                        <span class="px-3 py-1 bg-red-200 text-red-900 rounded-full font-bold">❌ FULL</span>
                                    @elseif($remaining < $predicted_evacuees * 0.2)
                                        <span class="px-3 py-1 bg-yellow-200 text-yellow-900 rounded-full font-semibold">⚠️ Limited capacity</span>
                                    @else
                                        <span class="px-3 py-1 bg-green-200 text-green-900 rounded-full font-semibold">✅ OK</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- The full ranking table has been moved into the combined prescriptive analytics card -->
           
        </table>
    </div>

</div>

{{-- Prescriptive allocations are displayed inside the combined analytics card above --}}


<div class="card">
    <div class="card-header">📋 Evacuation Areas List</div>
    
    @if($evacuationAreas->count() > 0)
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Address</th>
                        <th>Capacity</th>
                        <th>Occupancy</th>
                        <th>Status</th>
                        <th>Recommendation</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($evacuationAreas as $area)
                        <tr>
                            <td><strong>{{ $area->name }}</strong></td>
                            <td>{{ $area->address }}</td>
                            <td>{{ $area->capacity }}</td>
                            <td>
                                {{ $area->current_occupancy }} / {{ $area->capacity }}
                                <div class="progress-bar-container">
                                    <div class="progress-bar {{ $area->occupancy_percentage >= 90 ? 'danger' : ($area->occupancy_percentage >= 70 ? 'warning' : 'success') }}" style="width: {{ $area->occupancy_percentage }}%;"></div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-{{ $area->status == 'available' ? 'success' : ($area->status == 'full' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($area->status) }}
                                </span>
                            </td>
                            
                            <td>
                                @php
                                    $remaining = $area->capacity - $area->current_occupancy;
                                    $predicted = $predicted_evacuees ?? 0;
                                @endphp

                                @if($remaining >= $predicted)
                                    <span class="badge badge-success">Evacuate here</span>
                                @else
                                    <span class="badge badge-danger">Find alternate area</span>
                                @endif
                            </td>

                            <td>
                                <a href="{{ route('evacuation-areas.show', $area) }}" class="btn btn-primary btn-small">View</a>
                                <button onclick='goToEvacuationArea({{ $area->id }}, {!! json_encode($area->name) !!})' class="btn btn-success btn-small">Go</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-muted p-1">No evacuation areas found.</p>
    @endif
</div>

<!-- Add Location as Evacuation Area Modal -->
<div id="addLocationModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 600px; max-height: 90vh; overflow-y: auto;">
        <h3 class="modal-title">➕ Add My Location as Evacuation Area</h3>
        <form id="addLocationForm">
            <input type="hidden" id="newAreaLatitude" name="latitude">
            <input type="hidden" id="newAreaLongitude" name="longitude">
            
            <div class="form-group">
                <label class="form-label">Evacuation Area Name *</label>
                <input type="text" name="name" class="form-control" required placeholder="e.g., Community Center, School Gym">
            </div>
            
            <div class="form-group">
                <label class="form-label">Address *</label>
                <input type="text" name="address" class="form-control" required placeholder="Full address of the evacuation area">
            </div>
            
            <div class="form-group">
                <label class="form-label">Coordinates (Auto-filled from your location)</label>
                <input type="text" id="coordinatesDisplay" class="form-control" readonly style="background: #f0f0f0;">
            </div>
            
            <div class="form-group">
                <label class="form-label">Capacity (Number of People) *</label>
                <input type="number" name="capacity" class="form-control" min="1" required placeholder="Maximum number of people">
            </div>
            
            <!-- Disaster type is not captured in this modal -->
            
            <div class="form-group">
                <label class="form-label">Facilities (Optional)</label>
                <textarea name="facilities" class="form-control" rows="2" placeholder="e.g., Restrooms, Kitchen, Medical supplies"></textarea>
            </div>
            
            <div class="form-group">
                <label class="form-label">Contact Number (Optional)</label>
                <input type="text" name="contact_number" class="form-control" placeholder="Contact number for this evacuation area">
            </div>
            
            <div class="flex-gap">
                <button type="submit" class="btn btn-success flex-1">✓ Add Evacuation Area</button>
                <button type="button" onclick="closeAddLocationModal()" class="btn btn-secondary flex-1">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Go Modal -->
<div id="goModal" class="modal-overlay">
    <div class="modal-content">
        <h3 class="modal-title">Register to Evacuation Area</h3>
        <form id="goForm" action="" method="POST">
    @csrf

            <input type="hidden" id="evacuationAreaId" name="evacuation_area_id">

            <div class="form-group">
                <label class="form-label">Family Name</label>
                <input type="text" name="family_head_name" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Number of Family Members</label>
                <input type="number" name="total_members" class="form-control" min="1" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Contact Number</label>
                <input type="text" name="contact_number" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">Home Address</label>
                <textarea name="address" class="form-control" rows="3"></textarea>
            </div>
            
            <div class="form-group">
                <label class="form-label">Special Needs (Optional)</label>
                <textarea name="special_needs" class="form-control" rows="3"></textarea>
            </div>
            
            <div class="flex-gap">
                <button type="submit" class="btn btn-success flex-1">Confirm & Go</button>
                <button type="button" onclick="closeGoModal()" class="btn btn-secondary flex-1">Cancel</button>
            </div>
        </form>
    </div>
</div>

@endsection

<style>
/* Prescriptive details panel: smooth expand/collapse */
#prescriptiveDetails.prescriptive-panel {
    max-height: 0; 
    overflow: hidden;
    transition: max-height 300ms ease, opacity 200ms ease;
    opacity: 0;
}
#prescriptiveDetails.prescriptive-panel.open {
    max-height: 1200px; /* large enough for content */
    opacity: 1;
}
.prescriptive-icon { transition: transform 200ms ease; display: inline-block; }
.prescriptive-icon.open { transform: rotate(-180deg); }
.btn-disabled { opacity: 0.55; cursor: not-allowed; }
.badge-small { font-size: 0.85rem; }
.badge-small.bg-gray-100 { background: #f7fafc; border: 1px solid #e2e8f0; }

/* Helpful small improvements for details panel inside the card */
.card .prescriptive-panel .card { box-shadow: none; }
</style>

@section('scripts')
<script>

    document.addEventListener('DOMContentLoaded', () => {
        const expandBtn = document.getElementById('prescriptiveExpandBtn');
        const details = document.getElementById('prescriptiveDetails');
        const countEl = document.getElementById('prescriptiveDetailsCount');
        const labelEl = expandBtn ? expandBtn.querySelector('.prescriptive-label') : null;
        const iconEl = expandBtn ? expandBtn.querySelector('.prescriptive-icon') : null;

        if (countEl) {
            try {
                const allocCount = (prescriptive && prescriptive.allocations) ? prescriptive.allocations.length : 0;
                countEl.textContent = allocCount;
            } catch (err) {
                countEl.textContent = '0';
            }
        }

        if (expandBtn && details) {
            // Disable the details button if there's nothing to show
            const allocCount = (prescriptive && prescriptive.allocations) ? prescriptive.allocations.length : 0;
            const recommendedCanAccommodate = prescriptive && prescriptive.recommended_can_accommodate;
            if (allocCount === 0 && recommendedCanAccommodate) {
                expandBtn.disabled = true;
                expandBtn.classList.add('btn-disabled');
                expandBtn.setAttribute('title', 'No allocation details needed — the recommended area can accommodate everyone');
            }

            // Enhance the expand/collapse behavior with animation, icon rotation, scroll into view
            expandBtn.addEventListener('click', () => {
                if (expandBtn.disabled) return;
                const isOpen = details.classList.toggle('open');
                // Set aria attributes and update label/icon
                expandBtn.setAttribute('aria-expanded', isOpen);
                details.setAttribute('aria-hidden', !isOpen);
                if (labelEl) labelEl.textContent = isOpen ? 'Hide Details' : 'Details';
                if (iconEl) iconEl.classList.toggle('open', isOpen);

                if (isOpen) {
                    // Ensure the details are in view
                    setTimeout(() => {
                        details.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 200);
                }
            });
        }

        // Accept plan removed: this UI element was a prototype and has been disabled/removed.
    });

    // Sparkline rendering moved below initialization of predictionTrend
    let map;
    let userMarker;
    let userLocation = null;
    let routingControl = null;
    const evacuationAreas = {!! json_encode($evacuationAreas) !!};
    const disasterPredictions = {!! json_encode($disasterPredictions) !!};
    const prescriptive = {!! json_encode($prescriptive ?? null) !!};
    const predictionTrend = {!! json_encode($prediction_trend ?? []) !!};
    let predictionMarkers = [];
    let faultLineLayer = null;
    
    // Initialize map
    map = L.map('map').setView([14.5995, 120.9842], 13); // Default: Manila
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    // Custom icons
    const availableIcon = L.divIcon({
        html: '<div style="background: #28a745; width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"></div>',
        className: '',
        iconSize: [30, 30]
    });
    
    const fullIcon = L.divIcon({
        html: '<div style="background: #dc3545; width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"></div>',
        className: '',
        iconSize: [30, 30]
    });
    
    const userIcon = L.divIcon({
        html: '<div style="background: #667eea; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"></div>',
        className: '',
        iconSize: [20, 20]
    });
    
    // Philippine Fault Lines (Major faults)
    const philippineFaultLines = [
        {
            name: "Philippine Fault Zone - Northern Segment",
            coordinates: [
                [17.5, 121.5], [17.0, 121.8], [16.5, 121.9], [16.0, 121.8],
                [15.5, 121.7], [15.0, 121.5], [14.5, 121.3]
            ]
        },
        {
            name: "Philippine Fault Zone - Central Segment",
            coordinates: [
                [14.5, 121.3], [14.0, 121.2], [13.5, 121.1], [13.0, 121.0],
                [12.5, 120.9], [12.0, 120.8]
            ]
        },
        {
            name: "Philippine Fault Zone - Southern Segment",
            coordinates: [
                [12.0, 120.8], [11.5, 120.7], [11.0, 120.6], [10.5, 120.5],
                [10.0, 120.4], [9.5, 120.3], [9.0, 120.2]
            ]
        },
        {
            name: "Marikina Valley Fault System - West Valley Fault",
            coordinates: [
                [14.7, 121.0], [14.65, 121.02], [14.6, 121.04], [14.55, 121.06],
                [14.5, 121.08], [14.45, 121.10], [14.4, 121.12]
            ]
        },
        {
            name: "Marikina Valley Fault System - East Valley Fault",
            coordinates: [
                [14.7, 121.1], [14.65, 121.12], [14.6, 121.14], [14.55, 121.16],
                [14.5, 121.18], [14.45, 121.20], [14.4, 121.22]
            ]
        },
        {
            name: "Cotabato Fault",
            coordinates: [
                [7.5, 124.5], [7.3, 124.4], [7.1, 124.3], [6.9, 124.2],
                [6.7, 124.1], [6.5, 124.0]
            ]
        },
        {
            name: "Surigao Fault",
            coordinates: [
                [9.8, 125.5], [9.6, 125.4], [9.4, 125.3], [9.2, 125.2]
            ]
        },
        {
            name: "Central Negros Fault",
            coordinates: [
                [10.5, 123.0], [10.3, 122.9], [10.1, 122.8], [9.9, 122.7]
            ]
        }
    ];
    
    // Helper functions for disaster predictions
    function getRiskColor(riskLevel) {
        if (riskLevel >= 8) return '#dc3545'; // Critical - Red
        if (riskLevel >= 6) return '#fd7e14'; // High - Orange
        if (riskLevel >= 4) return '#ffc107'; // Moderate - Yellow
        return '#28a745'; // Low - Green
    }
    
    function getMarkerRadius(riskLevel) {
        return 6 + (riskLevel * 1.5); // Base 6px + 1.5px per risk level
    }
    
    function getDisasterIcon(disasterType) {
        const icons = {
            'earthquake': '🌍',
            'flood': '🌊',
            'typhoon': '🌀',
            'landslide': '⛰️'
        };
        return icons[disasterType] || '⚠️';
    }
    
    // Map of evacuation area id => marker
    const evacuationMarkers = {};

    // Layer group for prescriptive highlights
    let prescriptiveLayer = L.layerGroup().addTo(map);

    // Add markers for evacuation areas
    function addMarkers(areas) {
        areas.forEach(area => {
            const icon = area.status === 'available' ? availableIcon : fullIcon;
            const marker = L.marker([area.latitude, area.longitude], { icon: icon }).addTo(map);
            
            const popupContent = `
                <div style="min-width: 200px;">
                    <h4 style="margin-bottom: 0.5rem;">${area.name}</h4>
                    <p style="margin-bottom: 0.5rem; color: #666;">${area.address}</p>
                    <p style="margin-bottom: 0.5rem;"><strong>Capacity:</strong> ${area.current_occupancy} / ${area.capacity}</p>
                    <p style="margin-bottom: 0.5rem;"><strong>Status:</strong> <span style="color: ${area.status === 'available' ? '#28a745' : '#dc3545'}">${(area.status ?? '').toUpperCase()}</span></p>
                    <p style="margin-bottom: 0.5rem;"><strong>Hazard:</strong> ${area.risk_score ?? 0} / 10</p>
                    <p style="margin-bottom: 0.5rem;"><strong>Prescriptive Score:</strong> ${parseFloat(area.final_score || 0).toFixed(2)} / 10</p>
                    <!-- Disaster type intentionally not shown in popups -->
                    <a href="/evacuation-areas/${area.id}" class="btn btn-primary" style="width: 100%; margin-bottom: 0.5rem;">View Details</a>
                    <button onclick="showRoute(${area.latitude}, ${area.longitude})" class="btn btn-success" style="width: 100%;">Show Route</button>
                </div>
            `;
            
            marker.bindPopup(popupContent);
            // store marker
            evacuationMarkers[area.id] = marker;
        });
    }
    
    addMarkers(evacuationAreas);

    // highlight recommended shelters from prescriptive allocations
    function highlightRecommendedShelters(prescriptive) {
        prescriptiveLayer.clearLayers();
        if (!prescriptive || !prescriptive.allocations) return;
        prescriptive.allocations.forEach(a => {
            const id = a.evacuation_area_id;
            const marker = evacuationMarkers[id];
            if (!marker) return;
            const latlng = marker.getLatLng();
            // Add a pulsing circle marker or ring
            const ring = L.circle(latlng, {
                radius: 60,
                color: '#00BFFF',
                weight: 3,
                fill: false,
                dashArray: '6,6'
            });
            prescriptiveLayer.addLayer(ring);

            // Also add a larger marker overlay for visual prominence
            const highlightMarker = L.circleMarker(latlng, {
                radius: 12,
                color: '#0077cc',
                fillColor: '#00aaff',
                fillOpacity: 0.6
            });
            prescriptiveLayer.addLayer(highlightMarker);
        });
    }

    // Render highlight if we have prescriptive allocations
    highlightRecommendedShelters(prescriptive);
    // Highlight the top allocation differently (if present)
    if (prescriptive && prescriptive.recommended_by_allocation) {
        const topAllocArea = evacuationAreas.find(a => a.id === prescriptive.recommended_by_allocation.id);
        if (topAllocArea) {
            const marker = evacuationMarkers[topAllocArea.id];
            if (marker) {
                const latlng = marker.getLatLng();
                const topRing = L.circle(latlng, {
                    radius: 100,
                    color: '#34d399',
                    weight: 3,
                    fill: false,
                });
                prescriptiveLayer.addLayer(topRing);
            }
        }
    }

    // Draw sparkline if we have prediction trend data (after predictionTrend is initialized)
    try {
        if (predictionTrend && predictionTrend.length > 0 && document.getElementById('predictionSparkline')) {
            const labels = predictionTrend.map(p => p.label || '');
            const values = predictionTrend.map(p => parseFloat(p.value || 0));
            const ctx = document.getElementById('predictionSparkline').getContext('2d');
            // Use a minimal sparkline config
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102,126,234,0.10)',
                        borderWidth: 2,
                        pointRadius: 0,
                        tension: 0.3
                    }]
                },
                options: {
                    plugins: {legend: { display: false }},
                    elements: { point: { radius: 0 }},
                    scales: { x: { display: false }, y: { display: false }},
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { intersect: false, mode: 'index' }
                }
            });
        }
    } catch (err) {
        console.warn('Sparkline draw failed', err);
    }
    
    // Add disaster prediction markers
    function addPredictionMarkers(predictions) {
        // Clear existing prediction markers
        predictionMarkers.forEach(marker => map.removeLayer(marker));
        predictionMarkers = [];
        
        predictions.forEach(prediction => {
            const color = getRiskColor(prediction.risk_level);
            const radius = getMarkerRadius(prediction.risk_level);
            const icon = getDisasterIcon(prediction.disaster_type);
            
            // Create custom icon with pulsing effect for high risk
            const pulseClass = prediction.risk_level >= 7 ? 'pulse-marker' : '';
            const customIcon = L.divIcon({
                className: 'custom-disaster-marker',
                html: `
                    <div class="${pulseClass}" style="
                        background-color: ${color};
                        width: ${radius * 2}px;
                        height: ${radius * 2}px;
                        border-radius: 50%;
                        border: 2px solid white;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.4);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: ${radius * 1.2}px;
                        position: relative;
                    ">
                        ${icon}
                    </div>
                `,
                iconSize: [radius * 2, radius * 2],
                iconAnchor: [radius, radius]
            });
            
            const marker = L.marker([prediction.latitude, prediction.longitude], { 
                icon: customIcon,
                zIndexOffset: -100 // Place predictions below evacuation areas
            }).addTo(map);
            
            // Create popup content
            const popupContent = `
                <div style="min-width: 220px;">
                    <h4 style="margin: 0 0 0.5rem 0; color: ${color}; font-size: 1rem;">
                        ${icon} ${prediction.location_name}
                    </h4>
                    <p style="margin: 0.3rem 0; font-size: 0.9rem;">
                        <strong>Type:</strong> ${prediction.disaster_type.charAt(0).toUpperCase() + prediction.disaster_type.slice(1)}
                    </p>
                    <p style="margin: 0.3rem 0; font-size: 0.9rem;">
                        <strong>Risk Level:</strong> 
                        <span style="background: ${color}; color: white; padding: 0.15rem 0.4rem; border-radius: 3px; font-weight: bold; font-size: 0.85rem;">
                            ${prediction.risk_level}/10
                        </span>
                    </p>
                    ${prediction.predicted_recovery_days ? `
                        <p style="margin: 0.3rem 0; font-size: 0.9rem;">
                            <strong>Est. Recovery:</strong> ~${prediction.predicted_recovery_days} days
                        </p>
                    ` : ''}
                    <p style="margin: 0.3rem 0; font-size: 0.8rem; color: #666;">
                        <strong>Predicted:</strong> ${new Date(prediction.predicted_at).toLocaleDateString()}
                    </p>
                </div>
            `;
            
            marker.bindPopup(popupContent);
            predictionMarkers.push(marker);
        });
    }
    
    // Function to show/hide fault lines
    function toggleFaultLines(show) {
        if (show) {
            if (!faultLineLayer) {
                faultLineLayer = L.layerGroup();
                
                philippineFaultLines.forEach(fault => {
                    const polyline = L.polyline(fault.coordinates, {
                        color: '#dc3545',
                        weight: 3,
                        opacity: 0.7,
                        dashArray: '10, 10'
                    });
                    
                    polyline.bindPopup(`
                        <div style="min-width: 180px;">
                            <h4 style="margin: 0 0 0.5rem 0; color: #dc3545; font-size: 0.95rem;">
                                ⚠️ ${fault.name}
                            </h4>
                            <p style="margin: 0; font-size: 0.85rem;">
                                Major earthquake fault line
                            </p>
                        </div>
                    `);
                    
                    faultLineLayer.addLayer(polyline);
                });
            }
            faultLineLayer.addTo(map);
        } else {
            if (faultLineLayer) {
                map.removeLayer(faultLineLayer);
            }
        }
    }
    
    // Initial render of predictions
    addPredictionMarkers(disasterPredictions);
    
    // Auto-check fault lines if there are earthquake predictions
    const hasEarthquakes = disasterPredictions.some(p => p.disaster_type === 'earthquake');
    if (hasEarthquakes) {
        document.getElementById('showFaultLines').checked = true;
        toggleFaultLines(true);
    }
    
    // Get user location
    document.getElementById('getLocationBtn').addEventListener('click', function() {
        document.getElementById('locationStatus').textContent = 'Getting location...';
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                userLocation = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                
                if (userMarker) {
                    map.removeLayer(userMarker);
                }
                
                userMarker = L.marker([userLocation.lat, userLocation.lng], { icon: userIcon }).addTo(map);
                userMarker.bindPopup('<strong>You are here</strong>').openPopup();
                
                map.setView([userLocation.lat, userLocation.lng], 13);
                
                document.getElementById('locationStatus').textContent = '✓ Location found!';
                document.getElementById('locationStatus').style.color = '#28a745';
                
                // Show the "Add My Location" button
                document.getElementById('addMyLocationBtn').style.display = 'inline-block';
            }, function(error) {
                document.getElementById('locationStatus').textContent = '✗ Could not get location';
                document.getElementById('locationStatus').style.color = '#dc3545';
            });
        } else {
            document.getElementById('locationStatus').textContent = '✗ Geolocation not supported';
            document.getElementById('locationStatus').style.color = '#dc3545';
        }
    });
    
    // Show route
    function showRoute(destLat, destLng) {
        if (!userLocation || userLocation.lat == null || userLocation.lng == null) {
        alert('Please get your location first!');
        return;
    }

    if (routingControl) {
        map.removeControl(routingControl);
    }

    routingControl = L.Routing.control({
        waypoints: [
            L.latLng(userLocation.lat, userLocation.lng),
            L.latLng(destLat, destLng)
        ],
        routeWhileDragging: true,
        show: true
    }).addTo(map);
    }
    
    // Disaster type filtering removed as the column is no longer used.
    
    // Toggle predictions (show or hide all predictions regardless of type)
    document.getElementById('showPredictions').addEventListener('change', function() {
        if (this.checked) {
            addPredictionMarkers(disasterPredictions);
        } else {
            predictionMarkers.forEach(marker => map.removeLayer(marker));
            predictionMarkers = [];
        }
    });
    
    // Toggle fault lines
    document.getElementById('showFaultLines').addEventListener('change', function() {
        toggleFaultLines(this.checked);
    });


   


    
   
    // Go to evacuation area
  function goToEvacuationArea(areaId, areaName) {
    document.getElementById('evacuationAreaId').value = areaId;
    const goForm = document.getElementById('goForm');
    goForm.action = `/evacuation-areas/${areaId}/go`;
    // open modal
    document.getElementById('goModal').style.display = 'block';
}




    
    function closeGoModal() {
    document.getElementById('goModal').style.display = 'none';
}

    
    // Handle form submission
    // Submit GO (Register Family) form
  document.getElementById('goForm').addEventListener('submit', async function(event){
    event.preventDefault();

    const form = this;
    const submitBtn = form.querySelector('[type="submit"]');
    const originalBtnText = submitBtn ? submitBtn.textContent : null;
    if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = '⏳ Registering...'; }

    const formData = new FormData(form);
    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
    const token = tokenMeta ? tokenMeta.getAttribute('content') : null;

    try {
        // Ensure action is set (in case modal input was filled manually or JS didn't set it)
        if (!form.action || form.action.trim() === '') {
            const areaIdValue = form.querySelector('#evacuationAreaId')?.value;
            if (!areaIdValue) {
                alert('Could not determine evacuation area. Please open the form from the Evacuation Areas list and try again.');
                return;
            }
            form.action = `/evacuation-areas/${areaIdValue}/go`;
        }

        // Debug info for devs to inspect request/headers
        console.debug('GO form submitting:', { action: form.action, token: token });

        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        // Try to parse JSON safely, but also handle redirects and HTML responses
        let data = null;
        const contentType = response.headers.get('content-type') || '';

        if (contentType.includes('application/json')) {
            data = await response.json();
        } else {
            // If server returned HTML (maybe a redirect/login page), try to grab some text
            const text = await response.text();
            console.error('Server returned non-JSON response:', text);
            throw new Error('Server returned an unexpected response (status ' + response.status + ')');
        }

        if (!response.ok) {
            // Check common API error statuses and surface messages
                if (response.status === 419) {
                // Try to refresh session & token and retry once
                console.warn('Session expired (419). Attempting to refresh session and retry...');
                try {
                    const refresh = await fetch(window.location.href, { credentials: 'same-origin', headers: { 'Accept': 'text/html' } });
                    if (refresh.ok) {
                        const html = await refresh.text();
                        const tmp = document.createElement('div');
                        tmp.innerHTML = html;
                        const newMeta = tmp.querySelector('meta[name="csrf-token"]');
                        if (newMeta) {
                            const newToken = newMeta.getAttribute('content');
                            document.querySelector('meta[name="csrf-token"]').setAttribute('content', newToken);
                            // Retry original request once
                            const retryFormData = new FormData(form);
                            const retryResp = await fetch(form.action, {
                                method: 'POST',
                                body: retryFormData,
                                credentials: 'same-origin',
                                headers: {
                                    'X-CSRF-TOKEN': newToken,
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });
                            if (retryResp.ok) {
                                const retryData = await retryResp.json();
                                if (retryData.success) {
                                    alert(retryData.message || 'Family successfully registered!');
                                    closeGoModal();
                                    location.reload();
                                    return;
                                } else {
                                    alert(retryData.message || 'Error registering family.');
                                    return;
                                }
                            }
                        }
                    }
                } catch (er) {
                    console.error('Refresh & retry failed', er);
                }
                // Offer actionable guidance depending on hostname mismatch
                const hostname = window.location.hostname;
                const suggestedHost = 'localhost';
                let guidance = 'Session expired. Please refresh the page and try again.';
                if (hostname !== suggestedHost && hostname !== 'localhost') {
                    guidance = `Session expired. It looks like you're visiting the site using "${hostname}". ` +
                        `Browsers and session cookies are host-specific; to ensure the session cookie is sent, try using ${suggestedHost} instead of ${hostname} (e.g., http://${suggestedHost}:8001), or set SESSION_DOMAIN in your .env to ${hostname} and restart the server.`;
                }
                alert(guidance);
            } else if (response.status === 422) {
                const firstError = (data.errors && Object.values(data.errors).flat(1)[0]) || data.message;
                alert('Validation error: ' + (firstError || 'Please check your input.'));
            } else {
                alert(data.message || 'Server error: ' + response.status);
            }
            return;
        }

        // Success handling
        if (data && data.success) {
            alert(data.message || 'Family successfully registered!');
            closeGoModal();
            location.reload();
        } else {
            alert(data && data.message ? data.message : 'Error registering family.');
        }
    } catch (err) {
        // Log the error and show a friendly message
        console.error('AJAX error on GO form submit:', err);
        alert('An unexpected error occurred. Check console for details or refresh the page.');
    } finally {
        if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = originalBtnText; }
    }
});







    
    // Add My Location as Evacuation Area
    document.getElementById('addMyLocationBtn').addEventListener('click', function() {
        if (!userLocation) {
            alert('Please get your location first!');
            return;
        }
        
        // Pre-fill coordinates
        document.getElementById('newAreaLatitude').value = userLocation.lat;
        document.getElementById('newAreaLongitude').value = userLocation.lng;
        document.getElementById('coordinatesDisplay').value = `${userLocation.lat.toFixed(6)}, ${userLocation.lng.toFixed(6)}`;
        
        // Show modal
        document.getElementById('addLocationModal').style.display = 'flex';
    });

    
    
    function closeAddLocationModal() {
        document.getElementById('addLocationModal').style.display = 'none';
        document.getElementById('addLocationForm').reset();
    }
    
    // Handle add location form submission
    const addLocationForm = document.getElementById('addLocationForm');
    const predictedEvacuees = {{ json_encode($predicted_evacuees ?? 0) }};
    if (addLocationForm) {
        addLocationForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = this.querySelector('[type="submit"]');
            const originalText = submitBtn ? submitBtn.textContent : '';
            if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = '⏳ Adding...'; }

            const formData = new FormData(this);
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            try {
                const response = await fetch('/evacuation-areas', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                const isJson = response.headers.get('content-type')?.includes('application/json');
                const result = isJson ? await response.json() : null;

                if (response.ok && result && result.success) {
                    const newArea = result.evacuation_area;

                    // Add new area to local array so filters and maps work next time
                    evacuationAreas.push(newArea);

                    // Add marker to the map for the new area
                    addMarkers([newArea]);

                    // Add to the evacuation areas table in the UI
                    try {
                        const tableBody = document.querySelector('.card .table tbody');
                        if (tableBody) {
                            const remaining = (newArea.capacity - (newArea.current_occupancy || 0));
                            const recommendation = remaining >= predictedEvacuees
                                ? '<span class="badge badge-success">Evacuate here</span>'
                                : '<span class="badge badge-danger">Find alternate area</span>';

                            const occupancyPct = newArea.capacity > 0 ? Math.round(((newArea.current_occupancy || 0) / newArea.capacity) * 100) : 0;

                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td><strong>${newArea.name}</strong></td>
                                <td>${newArea.address}</td>
                                <td>${newArea.capacity}</td>
                                <td>${newArea.current_occupancy || 0} / ${newArea.capacity}
                                    <div class="progress-bar-container">
                                        <div class="progress-bar ${occupancyPct >= 90 ? 'danger' : (occupancyPct >= 70 ? 'warning' : 'success')}" style="width: ${occupancyPct}%;"></div>
                                    </div>
                                </td>
                                <td><span class="badge badge-${newArea.status === 'available' ? 'success' : (newArea.status === 'full' ? 'danger' : 'warning')}">${(newArea.status ?? '').charAt(0).toUpperCase() + (newArea.status ?? '').slice(1)}</span></td>
                                <td>${recommendation}</td>
                                <td>
                                    <a href="/evacuation-areas/${newArea.id}" class="btn btn-primary btn-small">View</a>
                                    <button onclick='goToEvacuationArea(${newArea.id}, ${JSON.stringify(newArea.name)})' class="btn btn-success btn-small">Go</button>
                                </td>
                            `;
                            tableBody.prepend(tr);
                        }
                    } catch (err) {
                        console.warn('Failed to append new row to table', err);
                    }

                    alert(result.message || '✓ Evacuation area added successfully!');
                    closeAddLocationModal();
                } else {
                    // Try to render validation errors
                    if (result && result.errors) {
                        const firstError = Object.values(result.errors).flat(1)[0];
                        alert('Validation error: ' + firstError);
                    } else {
                        alert('✗ Error: ' + (result?.message || 'Failed to add evacuation area'));
                    }
                }
            } catch (error) {
                console.error('Add location error', error);
                alert('An error occurred. Please try again.');
            } finally {
                if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = originalText; }
            }
        });
    }

    
</script>
@endsection
<style>
.hidden {
    display: none;
}

/* Pulsing highlight for recommended shelters */
.pulse-ring {
    border: 2px solid rgba(0,170,255,0.6);
    box-shadow: 0 0 8px rgba(0,170,255,0.6);
}

/* Allocation progress bar */
.progress {
    border-radius: 8px;
}

.hazard-badge {
    display: inline-block;
    min-width: 34px;
    padding: 0.2rem 0.5rem;
    border-radius: 9999px;
    text-align: center;
    font-weight: 700;
    font-size: 0.85rem;
}

.hazard-low { background: #dff7e5; color: #167f25; }
.hazard-moderate { background: #fff4cc; color: #7a5b00; }
.hazard-high { background: #fff0e0; color: #7a3b00; }
.hazard-critical { background: #ffe6e6; color: #8b1e1e; }

/* Card shadow for emphasis */
.card-shadow {
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08), 0 1px 4px rgba(15, 23, 42, 0.04);
    border: 1px solid rgba(15, 23, 42, 0.06);
}

/* Table - custom styling */
.table-custom {
    border-collapse: separate;
    border-spacing: 0;
    border: 1px solid #e6edf3;
    width: 100%;
}
.table-custom th,
.table-custom td {
    padding: 0.6rem 0.75rem;
    border-right: 1px solid #e6edf3;
    vertical-align: middle;
}
.table-custom th:last-child,
.table-custom td:last-child {
    border-right: none;
}
.table-custom thead th {
    background: linear-gradient(180deg, #f8fafc 0%, #eef2f6 100%);
    color: #374151;
    font-weight: 700;
    font-size: 0.875rem;
    border-bottom: 2px solid #e6edf3;
}
.table-custom tbody tr:hover {
    background: #f9fafb;
}
.table-custom tbody tr:nth-child(even) {
    background: #ffffff;
}
.table-custom tbody tr:nth-child(odd) {
    background: #fbfdfe;
}

/* Allocation-specific styles */
.table-custom-alloc td {
    padding-top: 0.6rem;
    padding-bottom: 0.6rem;
}
.table-custom-ranking td {
    padding-top: 0.45rem;
    padding-bottom: 0.45rem;
}

/* Attractive badges */
.badge-custom {
    display:inline-block; padding: 0.25rem 0.5rem; border-radius: 9999px; font-weight:600; border: 1px solid rgba(0,0,0,0.06);
}
.badge-success { background:#ecfdf5; color:#065f46; border-color: rgba(6,95,70,0.1) }
.badge-warning { background:#fff7ed; color:#92400e; border-color: rgba(146,64,14,0.08) }
.badge-danger { background:#fff1f2; color:#7f1d1d; border-color: rgba(127,29,29,0.08) }

/* subtle button style update for details */
.btn-outline { border: 1px solid #e2e8f0; background: white; color: #374151; }
.btn-outline:hover { background: #f8fafc }

</style>
