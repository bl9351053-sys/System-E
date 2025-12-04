@extends('layouts.app')

@section('title', 'Add Disaster Prediction')

@section('content')
<div class="card">
    <div class="card-header">🔮 Add Disaster Prediction</div>
    
    <form action="{{ route('disaster-predictions.store') }}" method="POST">
        @csrf
        
        <div class="form-group">
            <label class="form-label">Disaster Type *</label>
            <select name="disaster_type" class="form-control" required>
                <option value="">Select Type</option>
                <option value="flood" {{ old('disaster_type') == 'flood' ? 'selected' : '' }}>Flood</option>
                <option value="landslide" {{ old('disaster_type') == 'landslide' ? 'selected' : '' }}>Landslide</option>
                <option value="earthquake" {{ old('disaster_type') == 'earthquake' ? 'selected' : '' }}>Earthquake</option>
                <option value="typhoon" {{ old('disaster_type') == 'typhoon' ? 'selected' : '' }}>Typhoon</option>
            </select>
            @error('disaster_type')
                <small style="color: #dc3545;">{{ $message }}</small>
            @enderror
        </div>
        
        <div class="form-group">
            <label class="form-label">Location Name *</label>
            <input type="text" name="location_name" class="form-control" value="{{ old('location_name') }}" required>
            @error('location_name')
                <small style="color: #dc3545;">{{ $message }}</small>
            @enderror
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label class="form-label">Latitude *</label>
                <input type="number" step="0.00000001" name="latitude" class="form-control" value="{{ old('latitude') }}" required>
                @error('latitude')
                    <small style="color: #dc3545;">{{ $message }}</small>
                @enderror
            </div>
            
            <div class="form-group">
                <label class="form-label">Longitude *</label>
                <input type="number" step="0.00000001" name="longitude" class="form-control" value="{{ old('longitude') }}" required>
                @error('longitude')
                    <small style="color: #dc3545;">{{ $message }}</small>
                @enderror
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Risk Level (1-10) *</label>
            <input type="range" name="risk_level" id="riskLevel" class="form-control" min="1" max="10" value="{{ old('risk_level', 5) }}" required style="height: 40px;">
            <div style="display: flex; justify-content: space-between; margin-top: 0.5rem;">
                <span style="color: #28a745;">Low (1)</span>
                <span id="riskValue" style="font-weight: bold; font-size: 1.2rem;">5</span>
                <span style="color: #dc3545;">Critical (10)</span>
            </div>
            @error('risk_level')
                <small style="color: #dc3545;">{{ $message }}</small>
            @enderror
        </div>
        
        <div class="form-group">
            <label class="form-label">Predicted Recovery Days</label>
            <input type="number" name="predicted_recovery_days" class="form-control" value="{{ old('predicted_recovery_days') }}" min="0" placeholder="e.g., 7">
            <small style="color: #666;">Estimated days until recovery (no floods, debris cleared, etc.)</small>
            @error('predicted_recovery_days')
                <small style="color: #dc3545;">{{ $message }}</small>
            @enderror
        </div>
        
        <div class="form-group">
            <label class="form-label">Prediction Factors</label>
            <textarea name="prediction_factors" class="form-control" rows="4" placeholder="e.g., Heavy rainfall expected, Low-lying area, Poor drainage system">{{ old('prediction_factors') }}</textarea>
            @error('prediction_factors')
                <small style="color: #dc3545;">{{ $message }}</small>
            @enderror
        </div>
        
        <div class="form-group">
            <label class="form-label">Predicted At *</label>
            <input type="datetime-local" name="predicted_at" class="form-control" value="{{ old('predicted_at', now()->format('Y-m-d\TH:i')) }}" required>
            @error('predicted_at')
                <small style="color: #dc3545;">{{ $message }}</small>
            @enderror
        </div>
        
        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-success">✓ Save Prediction</button>
            <a href="{{ route('disaster-predictions.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    const riskLevelInput = document.getElementById('riskLevel');
    const riskValueDisplay = document.getElementById('riskValue');
    
    riskLevelInput.addEventListener('input', function() {
        riskValueDisplay.textContent = this.value;
        
        if (this.value >= 8) {
            riskValueDisplay.style.color = '#dc3545';
        } else if (this.value >= 6) {
            riskValueDisplay.style.color = '#fd7e14';
        } else if (this.value >= 4) {
            riskValueDisplay.style.color = '#ffc107';
        } else {
            riskValueDisplay.style.color = '#28a745';
        }
    });
</script>
@endsection
