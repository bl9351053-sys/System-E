@extends('layouts.app')

@section('title', 'Add Disaster Update')

@section('content')
<div class="card">
    <div class="card-header">📢 Add Disaster Update</div>
    
    <form action="{{ route('disaster-updates.store') }}" method="POST">
        @csrf
        
        <div class="form-group">
            <label class="form-label">Disaster Type *</label>
            <select name="disaster_type" class="form-control" required>
                <option value="">Select Type</option>
                <option value="typhoon" {{ old('disaster_type') == 'typhoon' ? 'selected' : '' }}>Typhoon</option>
                <option value="earthquake" {{ old('disaster_type') == 'earthquake' ? 'selected' : '' }}>Earthquake</option>
                <option value="flood" {{ old('disaster_type') == 'flood' ? 'selected' : '' }}>Flood</option>
                <option value="landslide" {{ old('disaster_type') == 'landslide' ? 'selected' : '' }}>Landslide</option>
            </select>
            @error('disaster_type')
                <small style="color: #dc3545;">{{ $message }}</small>
            @enderror
        </div>
        
        <div class="form-group">
            <label class="form-label">Title *</label>
            <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
            @error('title')
                <small style="color: #dc3545;">{{ $message }}</small>
            @enderror
        </div>
        
        <div class="form-group">
            <label class="form-label">Description *</label>
            <textarea name="description" class="form-control" rows="5" required>{{ old('description') }}</textarea>
            @error('description')
                <small style="color: #dc3545;">{{ $message }}</small>
            @enderror
        </div>
        
        <div class="form-group">
            <label class="form-label">Severity Level *</label>
            <select name="severity" class="form-control" required>
                <option value="">Select Severity</option>
                <option value="low" {{ old('severity') == 'low' ? 'selected' : '' }}>Low</option>
                <option value="moderate" {{ old('severity') == 'moderate' ? 'selected' : '' }}>Moderate</option>
                <option value="high" {{ old('severity') == 'high' ? 'selected' : '' }}>High</option>
                <option value="critical" {{ old('severity') == 'critical' ? 'selected' : '' }}>Critical</option>
            </select>
            @error('severity')
                <small style="color: #dc3545;">{{ $message }}</small>
            @enderror
        </div>
        
        <div class="form-group">
            <label class="form-label">Source</label>
            <input type="text" name="source" class="form-control" value="{{ old('source', 'PAGASA/PhiVolcs') }}">
            @error('source')
                <small style="color: #dc3545;">{{ $message }}</small>
            @enderror
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label class="form-label">Latitude (Optional)</label>
                <input type="number" step="0.00000001" name="latitude" class="form-control" value="{{ old('latitude') }}">
                @error('latitude')
                    <small style="color: #dc3545;">{{ $message }}</small>
                @enderror
            </div>
            
            <div class="form-group">
                <label class="form-label">Longitude (Optional)</label>
                <input type="number" step="0.00000001" name="longitude" class="form-control" value="{{ old('longitude') }}">
                @error('longitude')
                    <small style="color: #dc3545;">{{ $message }}</small>
                @enderror
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Issued At *</label>
            <input type="datetime-local" name="issued_at" class="form-control" value="{{ old('issued_at', now()->format('Y-m-d\TH:i')) }}" required>
            @error('issued_at')
                <small style="color: #dc3545;">{{ $message }}</small>
            @enderror
        </div>
        
        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-success">✓ Publish Update</button>
            <a href="{{ route('disaster-updates.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
