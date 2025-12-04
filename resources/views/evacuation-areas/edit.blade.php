@extends('layouts.app')

@section('title', 'Edit Evacuation Area')

@section('content')
<div class="card">
    <div class="card-header">✏️ Edit Evacuation Area</div>
    
    <form action="{{ route('evacuation-areas.update', $evacuationArea) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label class="form-label">Name *</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $evacuationArea->name) }}" required>
            @error('name')
                <small style="color: #dc3545;">{{ $message }}</small>
            @enderror
        </div>
        
        <div class="form-group">
            <label class="form-label">Address *</label>
            <textarea name="address" class="form-control" rows="3" required>{{ old('address', $evacuationArea->address) }}</textarea>
            @error('address')
                <small style="color: #dc3545;">{{ $message }}</small>
            @enderror
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label class="form-label">Latitude *</label>
                <input type="number" step="0.00000001" name="latitude" id="latitude" class="form-control" value="{{ old('latitude', $evacuationArea->latitude) }}" required>
                @error('latitude')
                    <small style="color: #dc3545;">{{ $message }}</small>
                @enderror
            </div>
            
            <div class="form-group">
                <label class="form-label">Longitude *</label>
                <input type="number" step="0.00000001" name="longitude" id="longitude" class="form-control" value="{{ old('longitude', $evacuationArea->longitude) }}" required>
                @error('longitude')
                    <small style="color: #dc3545;">{{ $message }}</small>
                @enderror
            </div>
        </div>
        
        <div style="margin-bottom: 1.5rem;">
            <button type="button" id="pickLocationBtn" class="btn btn-secondary">📍 Pick Location on Map</button>
            <small style="color: #666; margin-left: 1rem;">Click on the map to update coordinates</small>
        </div>
        
        <div id="map" style="height: 400px; border-radius: 12px; margin-bottom: 1.5rem; display: none;"></div>
        
        <div class="form-group">
            <label class="form-label">Capacity (Number of People) *</label>
            <input type="number" name="capacity" class="form-control" value="{{ old('capacity', $evacuationArea->capacity) }}" min="1" required>
            @error('capacity')
                <small style="color: #dc3545;">{{ $message }}</small>
            @enderror
        </div>
        
        <div class="form-group">
            <label class="form-label">Status *</label>
            <select name="status" class="form-control" required>
                <option value="available" {{ old('status', $evacuationArea->status) == 'available' ? 'selected' : '' }}>Available</option>
                <option value="full" {{ old('status', $evacuationArea->status) == 'full' ? 'selected' : '' }}>Full</option>
                <option value="closed" {{ old('status', $evacuationArea->status) == 'closed' ? 'selected' : '' }}>Closed</option>
            </select>
            @error('status')
                <small style="color: #dc3545;">{{ $message }}</small>
            @enderror
        </div>
        
        <!-- Disaster type field removed from edit form -->
        
        <div class="form-group">
            <label class="form-label">Facilities (Optional)</label>
            <textarea name="facilities" class="form-control" rows="3" placeholder="e.g., Medical station, Food supplies, Restrooms">{{ old('facilities', $evacuationArea->facilities) }}</textarea>
            @error('facilities')
                <small style="color: #dc3545;">{{ $message }}</small>
            @enderror
        </div>
        
        <div class="form-group">
            <label class="form-label">Contact Number (Optional)</label>
            <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number', $evacuationArea->contact_number) }}">
            @error('contact_number')
                <small style="color: #dc3545;">{{ $message }}</small>
            @enderror
        </div>
        
        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-success">✓ Update Evacuation Area</button>
            <a href="{{ route('evacuation-areas.show', $evacuationArea) }}" class="btn btn-secondary">Cancel</a>
            <form action="{{ route('evacuation-areas.destroy', $evacuationArea) }}" method="POST" style="margin-left: auto;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this evacuation area?')">🗑️ Delete</button>
            </form>
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
                // Initialize map with current location
                const lat = parseFloat(document.getElementById('latitude').value);
                const lng = parseFloat(document.getElementById('longitude').value);
                
                map = L.map('map').setView([lat, lng], 15);
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);
                
                // Add existing marker
                marker = L.marker([lat, lng]).addTo(map);
                marker.bindPopup('<strong>Current Location</strong>').openPopup();
                
                // Add click event
                map.on('click', function(e) {
                    const newLat = e.latlng.lat;
                    const newLng = e.latlng.lng;
                    
                    document.getElementById('latitude').value = newLat;
                    document.getElementById('longitude').value = newLng;
                    
                    if (marker) {
                        map.removeLayer(marker);
                    }
                    
                    marker = L.marker([newLat, newLng]).addTo(map);
                    marker.bindPopup(`<strong>New Location</strong><br>Lat: ${newLat.toFixed(6)}<br>Lng: ${newLng.toFixed(6)}`).openPopup();
                });
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
