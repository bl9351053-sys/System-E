@extends('layouts.app')

@section('title', $disasterUpdate->title)

@section('content')
<div class="card">
    <div class="flex-between mb-15">
        <div class="card-header card-header-no-border">🚨 Disaster Update Details</div>
        <a href="{{ route('disaster-updates.index') }}" class="btn btn-secondary">← Back</a>
    </div>
    
    <div class="severity-{{ $disasterUpdate->severity == 'critical' ? 'critical' : ($disasterUpdate->severity == 'high' ? 'high' : 'moderate') }}-bg mb-15">
        <h2 class="detail-title">{{ $disasterUpdate->title }}</h2>
        
        <div class="flex-gap mb-1">
            <span class="badge badge-{{ $disasterUpdate->severity == 'critical' ? 'critical' : ($disasterUpdate->severity == 'high' ? 'danger' : ($disasterUpdate->severity == 'moderate' ? 'warning' : 'info')) }}">
                {{ ucfirst($disasterUpdate->severity) }} Severity
            </span>
            <span class="badge badge-info">{{ ucfirst($disasterUpdate->disaster_type) }}</span>
        </div>
        
        <p class="text-muted text-large">{{ $disasterUpdate->description }}</p>
        
        <div class="border-top-divider">
            <p class="mb-05"><strong>Source:</strong> {{ $disasterUpdate->source }}</p>
            <p class="mb-05"><strong>Issued:</strong> {{ $disasterUpdate->issued_at->format('F d, Y h:i A') }}</p>
            <p class="mb-0"><strong>Time:</strong> {{ $disasterUpdate->issued_at->diffForHumans() }}</p>
        </div>
    </div>
    
    @if($disasterUpdate->latitude && $disasterUpdate->longitude)
        <div class="card">
            <div class="card-header">📍 Affected Location</div>
            <div id="map"></div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
@if($disasterUpdate->latitude && $disasterUpdate->longitude)
<script>
    const map = L.map('map').setView([{{ $disasterUpdate->latitude }}, {{ $disasterUpdate->longitude }}], 12);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    const marker = L.marker([{{ $disasterUpdate->latitude }}, {{ $disasterUpdate->longitude }}]).addTo(map);
    marker.bindPopup('<strong>{{ $disasterUpdate->title }}</strong><br>{{ ucfirst($disasterUpdate->disaster_type) }}').openPopup();
</script>
@endif
@endsection
