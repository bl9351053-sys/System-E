@extends('layouts.app')

@section('title', 'Disaster Predictions')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/disaster-predictions.css') }}">
@endpush

@section('content')
<div class="card">
    <div class="flex-between mb-15">
        <div class="card-header card-header-no-border">🔮 Disaster Predictions & Recovery Estimates</div>
        <a href="{{ route('real-time-data.sync') }}" class="btn btn-success" onclick="event.preventDefault(); syncPredictions(this);">🔄 Sync from PAGASA/PhiVolcs</a>
    </div>
    
    <div class="info-box warning mb-15">
        <strong>📡 Data Sources:</strong> Predictions are automatically generated from real-time data provided by:
        <strong>PAGASA</strong> (weather & rainfall), <strong>PhiVolcs</strong> (earthquakes, volcanoes & fault lines), and <strong>NDRRMC</strong> (disaster management).
        Click "Sync" to fetch the latest predictions.
    </div>
    
    @if($predictions->count() > 0)
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Location</th>
                        <th>Disaster Type</th>
                        <th>Risk Level</th>
                        <th>Recovery Days</th>
                        <th>Predicted At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($predictions as $prediction)
                        <tr class="{{ $prediction->risk_level >= 8 ? 'risk-row-critical' : ($prediction->risk_level >= 6 ? 'risk-row-high' : 'risk-row-normal') }}">
                            <td>
                                <strong>{{ $prediction->location_name }}</strong>
                                <br>
                                <small class="location-coordinates">{{ $prediction->latitude }}, {{ $prediction->longitude }}</small>
                            </td>
                            <td>
                                <span class="badge badge-info">{{ ucfirst($prediction->disaster_type) }}</span>
                            </td>
                            <td>
                                <div class="risk-level-container">
                                    <span class="badge badge-{{ $prediction->risk_level >= 8 ? 'critical' : ($prediction->risk_level >= 6 ? 'danger' : ($prediction->risk_level >= 4 ? 'warning' : 'success')) }}">
                                        {{ $prediction->risk_level }}/10
                                    </span>
                                    <div class="risk-bar-background">
                                        <div class="risk-bar-fill {{ $prediction->risk_level >= 8 ? 'risk-bar-critical' : ($prediction->risk_level >= 6 ? 'risk-bar-high' : ($prediction->risk_level >= 4 ? 'risk-bar-medium' : 'risk-bar-low')) }}" style="width: {{ $prediction->risk_level * 10 }}%;"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($prediction->predicted_recovery_days)
                                    <strong>~{{ $prediction->predicted_recovery_days }} days</strong>
                                @else
                                    <span class="recovery-na">N/A</span>
                                @endif
                            </td>
                            <td>{{ $prediction->predicted_at?->format('M d, Y') ?? 'N/A' }}</td>

                            <td>
                                <a href="{{ route('disaster-predictions.show', $prediction) }}" class="btn btn-primary action-btn">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="pagination-container">
            {{ $predictions->links() }}
        </div>
    @else
        <p class="no-predictions">No predictions available.</p>
    @endif
</div>

<div class="card">
    <div class="card-header">📊 Risk Analysis by Location</div>
    <canvas id="riskChart"></canvas>
</div>
@endsection

@section('scripts')
<script>
    const predictions = {!! json_encode($predictions->items()) !!};
    
    const riskCtx = document.getElementById('riskChart').getContext('2d');
    const riskChart = new Chart(riskCtx, {
        type: 'bar',
        data: {
            labels: predictions.map(p => p.location_name),
            datasets: [{
                label: 'Risk Level',
                data: predictions.map(p => p.risk_level),
                backgroundColor: predictions.map(p => {
                    if (p.risk_level >= 8) return '#dc3545';
                    if (p.risk_level >= 6) return '#fd7e14';
                    if (p.risk_level >= 4) return '#ffc107';
                    return '#28a745';
                })
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 10
                }
            }
        }
    });

    // Sync predictions from PAGASA/PhiVolcs/NDRRMC
    async function syncPredictions(button) {
        // `button` is the <a> element passed from the onclick handler
        const originalText = button.textContent;
        button.disabled = true;
        button.textContent = '⏳ Syncing from PAGASA/PhiVolcs...';

        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        const token = tokenMeta ? tokenMeta.getAttribute('content') : null;

        try {
            const response = await fetch('http://localhost:8001/api/real-time-data/sync', {
    method: 'POST',
    headers: {
        'Accept': 'application/json',
        'Authorization': 'Bearer token12345', // your API token from .env
    }
});

            let result = null;
            try { result = await response.json(); } catch (err) { /* ignore parse errors */ }

            if (response.ok && result && result.success) {
                alert('✓ Successfully synced predictions from PAGASA, PhiVolcs, and NDRRMC!\n\n' + (result.message || ''));
                location.reload();
            } else if (result && !result.success) {
                alert('✗ Error: ' + (result.message || 'Failed to sync'));
            } else {
                alert('Error syncing predictions. Server returned unexpected response.');
            }
        } catch (error) {
            alert('Error syncing predictions. Please try again.');
            console.error(error);
        } finally {
            button.disabled = false;
            button.textContent = originalText;
        }
    }
</script>
@endsection
