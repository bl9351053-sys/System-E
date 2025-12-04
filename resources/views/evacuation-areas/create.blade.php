@extends('layouts.app')

@section('title', 'Add Evacuation Area')

@section('content')
<div class="card">
    <div class="card-header">➕ Add New Evacuation Area</div>
    
    <form action="{{ route('evacuation-areas.store') }}" method="POST">
        @csrf
        
        <div class="form-group">
            <label class="form-label">Name *</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            @error('name')
                <small style="color: #dc3545;">{{ $message }}</small>
            @enderror
        </div>
        
        <div class="form-group">
            <label class="form-label">Address *</label>
            <textarea name="address" class="form-control" rows="3" required>{{ old('address') }}</textarea>
            @error('address')
                <small style="color: #dc3545;">{{ $message }}</small>
            @enderror
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label class="form-label">Latitude *</label>
                <input type="number" step="0.00000001" name="latitude" id="latitude" class="form-control" value="{{ old('latitude') }}" required>
                @error('latitude')
                    <small style="color: #dc3545;">{{ $message }}</small>
                @enderror
            </div>
            
            <div class="form-group">
                <label class="form-label">Longitude *</label>
                <input type="number" step="0.00000001" name="longitude" id="longitude" class="form-control" value="{{ old('longitude') }}" required>
                @error('longitude')
                    <small style="color: #dc3545;">{{ $message }}</small>
                @enderror
            </div>
        </div>
        
        <div style="margin-bottom: 1.5rem;">
            <button type="button" id="pickLocationBtn" class="btn btn-secondary">📍 Pick Location on Map</button>
            <small style="color: #666; margin-left: 1rem;">Click on the map to set coordinates</small>
        </div>
        
        <div id="map" style="height: 400px; border-radius: 12px; margin-bottom: 1.5rem; display: none;"></div>
        
        <div class="form-group">
            <label class="form-label">Capacity (Number of People) *</label>
            <input type="number" name="capacity" class="form-control" value="{{ old('capacity') }}" min="1" required>
            @error('capacity')
                <small style="color: #dc3545;">{{ $message }}</small>
            @enderror
        </div>
        
        <!-- Removed disaster_type field: column no longer used -->
        
        <div class="form-group">
            <label class="form-label">Facilities (Optional)</label>
            <textarea name="facilities" class="form-control" rows="3" placeholder="e.g., Medical station, Food supplies, Restrooms">{{ old('facilities') }}</textarea>
            @error('facilities')
                <small style="color: #dc3545;">{{ $message }}</small>
            @enderror
        </div>
        
        <div class="form-group">
            <label class="form-label">Contact Number (Optional)</label>
            <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number') }}">
            @error('contact_number')
                <small style="color: #dc3545;">{{ $message }}</small>
            @enderror
        </div>
        
        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-success">✓ Save Evacuation Area</button>
            <a href="{{ route('evacuation-areas.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    let map;
    let marker;
    let mapVisible = false;
    
    document.getElementById('pickLocationBtn').addEventListener('click', function() {
        mapVisible = !mapVisible;
        const mapElement = document.getElementById('map');
        
        if (mapVisible) {
            mapElement.style.display = 'block';
            this.textContent = '🗺️ Hide Map';
            
            if (!map) {
                // Initialize map
                map = L.map('map').setView([14.5995, 120.9842], 13);
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);
                
                // Add click event
                map.on('click', function(e) {
                    const lat = e.latlng.lat;
                    const lng = e.latlng.lng;
                    
                    document.getElementById('latitude').value = lat;
                    document.getElementById('longitude').value = lng;
                    
                    if (marker) {
                        map.removeLayer(marker);
                    }
                    
                    marker = L.marker([lat, lng]).addTo(map);
                    marker.bindPopup(`<strong>Selected Location</strong><br>Lat: ${lat.toFixed(6)}<br>Lng: ${lng.toFixed(6)}`).openPopup();
                });
                
                // Try to get user's location
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        map.setView([position.coords.latitude, position.coords.longitude], 13);
                    });
                }
            }
            
            setTimeout(() => {
                map.invalidateSize();
            }, 100);
        } else {
            mapElement.style.display = 'none';
            this.textContent = '📍 Pick Location on Map';
        }
    });
</script>
@endsection
