@extends('layouts.app')

@section('title', $evacuationArea->name)

@section('content')
<div class="card">
    <div class="flex-between mb-15">
        <div class="card-header card-header-no-border">🏢 {{ $evacuationArea->name }}</div>
        <div class="flex-gap-small">
            <a href="{{ route('evacuation-areas.edit', $evacuationArea) }}" class="btn btn-warning">✏️ Edit</a>
            <a href="{{ route('evacuation-areas.index') }}" class="btn btn-secondary">← Back</a>
        </div>
    </div>
    
    <div class="grid-2col">
        <div>
            <h3 class="detail-title">Details</h3>
            
            <div class="mb-1">
                <strong>Address:</strong>
                <p class="text-muted" style="margin-top: 0.25rem;">{{ $evacuationArea->address }}</p>
            </div>
            
            <div class="mb-1">
                <strong>Coordinates:</strong>
                <p class="text-muted" style="margin-top: 0.25rem;">{{ $evacuationArea->latitude }}, {{ $evacuationArea->longitude }}</p>
            </div>
            
            <div class="mb-1">
                <strong>Capacity:</strong>
                <p class="text-muted" style="margin-top: 0.25rem;">{{ $evacuationArea->capacity }} people</p>
            </div>
            
            <div class="mb-1">
                <strong>Current Occupancy:</strong>
                <p class="text-muted" style="margin-top: 0.25rem;">{{ $evacuationArea->current_occupancy }} / {{ $evacuationArea->capacity }} ({{ $evacuationArea->occupancy_percentage }}%)</p>
                <div class="progress-bar-container" style="height: 20px; border-radius: 10px; margin-top: 0.5rem;">
                    <div class="progress-bar {{ $evacuationArea->occupancy_percentage >= 90 ? 'danger' : ($evacuationArea->occupancy_percentage >= 70 ? 'warning' : 'success') }}" style="width: {{ $evacuationArea->occupancy_percentage }}%; border-radius: 10px; transition: width 0.3s;"></div>
                </div>
            </div>
            
            <div class="mb-1">
                <strong>Available Space:</strong>
                <p class="text-muted" style="margin-top: 0.25rem;">{{ $evacuationArea->available_space }} people</p>
            </div>
            
            <div class="mb-1">
                <strong>Status:</strong>
                <p style="margin-top: 0.25rem;">
                    <span class="badge badge-{{ $evacuationArea->status == 'available' ? 'success' : ($evacuationArea->status == 'full' ? 'danger' : 'warning') }}">
                        {{ ucfirst($evacuationArea->status) }}
                    </span>
                </p>
            </div>
            
            <!-- Disaster type removed from UI -->
            
            @if($evacuationArea->facilities)
                <div class="mb-1">
                    <strong>Facilities:</strong>
                    <p class="text-muted" style="margin-top: 0.25rem;">{{ $evacuationArea->facilities }}</p>
                </div>
            @endif
            
            @if($evacuationArea->contact_number)
                <div class="mb-1">
                    <strong>Contact Number:</strong>
                    <p class="text-muted" style="margin-top: 0.25rem;">{{ $evacuationArea->contact_number }}</p>
                </div>
            @endif
        </div>
        
        <div>
            <h3 class="detail-title">Location</h3>
            <div id="map"></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">👨‍👩‍👧‍👦 Families ({{ $evacuationArea->families->count() }})</div>
    
    @if($evacuationArea->families->count() > 0)
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Family Name</th>
                        <th>Members</th>
                        <th>Contact</th>
                        <th>Special Needs</th>
                        <th>Checked In</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($evacuationArea->families->whereNull('checked_out_at') as $family)
                        <tr>
                            <td><strong>{{ $family->family_head_name }}</strong></td>
                            <td>{{ $family->total_members }}</td>
                            <td>{{ $family->contact_number }}</td>
                            <td>{{ $family->special_needs ?? 'None' }}</td>
                            <td>{{ $family->checked_in_at?->format('M d, Y h:i A') ?? 'Not Checked In' }}</td>

                            <td>
                                {{-- Check Out button removed; manage check-ins/outs in System-E Admin only --}}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-muted p-1">No families currently in this evacuation area.</p>
    @endif
</div>
@endsection

@section('scripts')
<script>
    const map = L.map('map').setView([{{ $evacuationArea->latitude }}, {{ $evacuationArea->longitude }}], 15);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    const marker = L.marker([{{ $evacuationArea->latitude }}, {{ $evacuationArea->longitude }}]).addTo(map);
    marker.bindPopup('<strong>{{ $evacuationArea->name }}</strong><br>{{ $evacuationArea->address }}').openPopup();
</script>
@endsection
