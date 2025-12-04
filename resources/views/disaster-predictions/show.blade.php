@extends('layouts.app')

@section('title', $disasterPrediction->location_name ?? 'Prediction Details')

@section('content')
<div class="card">
    <div class="flex-between mb-15">
        <div class="card-header card-header-no-border">🔮 Disaster Prediction Details</div>
        <a href="{{ route('disaster-predictions.index') }}" class="btn btn-secondary">← Back</a>
    </div>
    
    <div class="severity-{{ $disasterPrediction->risk_level ?? 0 >= 8 ? 'critical' : ($disasterPrediction->risk_level ?? 0 >= 6 ? 'high' : 'moderate') }}-bg mb-15">
        <h2 class="detail-title">{{ $disasterPrediction->location_name ?? 'N/A' }}</h2>
        
        <div class="flex-gap mb-15">
            <span class="badge badge-info">{{ ucfirst($disasterPrediction->disaster_type ?? 'Unknown') }}</span>
            <span class="badge badge-{{ $disasterPrediction->risk_level ?? 0 >= 8 ? 'critical' : ($disasterPrediction->risk_level ?? 0 >= 6 ? 'danger' : ($disasterPrediction->risk_level ?? 0 >= 4 ? 'warning' : 'success')) }}">
                Risk Level: {{ $disasterPrediction->risk_level ?? 'N/A' }}/10
            </span>
        </div>
        
        @if($disasterPrediction->predicted_recovery_days)
            <div class="info-box mb-15">
                <h4 class="mb-05">⏱️ Estimated Recovery Time</h4>
                <p style="font-size: 1.5rem; color: #667eea; margin: 0;"><strong>~{{ $disasterPrediction->predicted_recovery_days }} days</strong></p>
                <small class="text-muted">Time until area recovers (no floods, debris cleared, etc.)</small>
            </div>
        @endif
        
        @if($disasterPrediction->prediction_factors)
            <div class="mb-15">
                <h4 class="mb-05">📋 Prediction Factors & Data Sources</h4>
                <p class="text-muted" style="line-height: 1.6;">{{ $disasterPrediction->prediction_factors }}</p>
                <div class="info-box mt-1">
                    <small><strong>ℹ️ Note:</strong> This prediction is automatically generated from real-time data provided by official Philippine government agencies: PAGASA (weather monitoring), PhiVolcs (seismic & volcanic activity), and NDRRMC (disaster management).</small>
                </div>
            </div>
        @endif
        
        <div class="pt-1" style="border-top: 2px solid #e0e0e0;">
            <p class="mb-05"><strong>Coordinates:</strong> {{ $disasterPrediction->latitude ?? 'N/A' }}, {{ $disasterPrediction->longitude ?? 'N/A' }}</p>
            <p class="mb-0"><strong>Predicted:</strong> 
                {{ $disasterPrediction->predicted_at?->format('F d, Y h:i A') ?? 'N/A' }} 
                ({{ $disasterPrediction->predicted_at?->diffForHumans() ?? 'N/A' }})
            </p>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">📍 Location Map</div>
        <div id="map" style="height: 400px;"></div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    @if($disasterPrediction->latitude && $disasterPrediction->longitude)
    const map = L.map('map').setView([{{ $disasterPrediction->latitude }}, {{ $disasterPrediction->longitude }}], 13);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    const marker = L.marker([{{ $disasterPrediction->latitude }}, {{ $disasterPrediction->longitude }}]).addTo(map);
    marker.bindPopup('<strong>{{ $disasterPrediction->location_name ?? "Location" }}</strong><br>{{ ucfirst($disasterPrediction->disaster_type ?? "Unknown") }} Risk: {{ $disasterPrediction->risk_level ?? "N/A" }}/10').openPopup();
    
    const circle = L.circle([{{ $disasterPrediction->latitude }}, {{ $disasterPrediction->longitude }}], {
        color: '{{ $disasterPrediction->risk_level ?? 0 >= 8 ? "#dc3545" : ($disasterPrediction->risk_level ?? 0 >= 6 ? "#fd7e14" : ($disasterPrediction->risk_level ?? 0 >= 4 ? "#ffc107" : "#28a745")) }}',
        fillColor: '{{ $disasterPrediction->risk_level ?? 0 >= 8 ? "#dc3545" : ($disasterPrediction->risk_level ?? 0 >= 6 ? "#fd7e14" : ($disasterPrediction->risk_level ?? 0 >= 4 ? "#ffc107" : "#28a745")) }}',
        fillOpacity: 0.2,
        radius: {{ ($disasterPrediction->risk_level ?? 0) * 500 }}
    }).addTo(map);
    @else
    document.getElementById('map').innerHTML = '<p class="text-center text-muted mt-3">No map available for this prediction.</p>';
    @endif
</script>
@endsection
