@extends('layouts.app')

@section('title', 'Dashboard - Evacuation Management System')

@section('content')
<div class="card">
    <div class="card-header">📊 Dashboard Overview</div>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">🏢</div>
            <div class="stat-info">
                <h3>{{ $totalEvacuationAreas }}</h3>
                <p>Total Evacuation Areas</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">👨‍👩‍👧‍👦</div>
            <div class="stat-info">
                <h3>{{ $totalFamilies }}</h3>
                <p>Families Evacuated</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-info">
                <h3>{{ $totalPeople }}</h3>
                <p>Total People</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-info">
                <h3>{{ $availableAreas }}</h3>
                <p>Available Areas</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">🚫</div>
            <div class="stat-info">
                <h3>{{ $fullAreas }}</h3>
                <p>Full Areas</p>
            </div>
        </div>
    </div>
</div>

<div class="grid-2col mb-15">
    <div class="card">
        <div class="card-header">📈 Disaster Type Distribution</div>
        <div style="height: 250px; position: relative; padding: 1rem; max-width: 300px; margin: 0 auto;">
            <canvas id="disasterTypeChart"></canvas>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">⚠️ Severity Levels</div>
        <div style="height: 250px; position: relative; padding: 1rem; max-width: 300px; margin: 0 auto;">
            <canvas id="severityChart"></canvas>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">🏢 Evacuation Area Occupancy</div>
    <div style="height: 280px; position: relative; padding: 1rem;">
        <canvas id="occupancyChart"></canvas>
    </div>
</div>
<div class="grid-2col">
    <div class="card">
        <div class="card-header">🚨 Recent Disaster Updates</div>
        @if($recentUpdates->count() > 0)
            <div class="scrollable-content">
                @foreach($recentUpdates as $update)
                    <div class="update-item">
                        <div class="flex-between">
                            <div>
                                <h4 class="card-title">{{ $update->title }}</h4>
                                <p class="text-muted text-small">{{ Str::limit($update->description, 100) }}</p>
                                <small class="text-light-muted">{{ $update->issued_at->diffForHumans() }}</small>
                            </div>
                            <span class="badge badge-{{ $update->severity == 'critical' ? 'critical' : ($update->severity == 'high' ? 'danger' : ($update->severity == 'moderate' ? 'warning' : 'info')) }}">
                                {{ ucfirst($update->severity) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-muted p-1">No recent disaster updates.</p>
        @endif
        <a href="{{ route('disaster-updates.index') }}" class="btn btn-primary mt-1 w-100">View All Updates</a>
    </div>
    <div class="grid-2col mb-15">
    
    <div class="card">
        <div class="card-header">🔮 Active Predictions</div>
        @if($activePredictions->count() > 0)
            <div class="scrollable-content">
                @foreach($activePredictions as $prediction)
                    <div class="update-item">
                        <div class="flex-between">
                            <div>
                                <h4 class="card-title">{{ ucfirst($prediction->disaster_type) }} - {{ $prediction->location_name }}</h4>
                                <p class="text-muted text-small">Risk Level: {{ $prediction->risk_level }}/10</p>
                                @if($prediction->predicted_recovery_days)
                                    <p class="text-muted text-small">Recovery: ~{{ $prediction->predicted_recovery_days }} days</p>
                                @endif
                                <small class="text-light-muted">{{ $prediction->predicted_at->diffForHumans() }}</small>
                            </div>
                            <span class="badge badge-{{ $prediction->risk_level >= 8 ? 'critical' : ($prediction->risk_level >= 6 ? 'danger' : 'warning') }}">
                                Level {{ $prediction->risk_level }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-muted p-1">No active predictions.</p>
        @endif
        <a href="{{ route('disaster-predictions.index') }}" class="btn btn-primary mt-1 w-100">View All Predictions</a>
    </div>
</div>
<div class="row mt-4">
    <!-- Table -->
    

    <!-- Chart -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">📈 Forecast Trend</div>
            <div style="height: 300px; padding: 1rem;">
                <canvas id="forecastChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">🔮 Prescriptive Recommendation</div>

    @if($topRecommendedArea)
        <div class="p-3 mb-2" style="border-bottom: 1px solid #eee;">
            <h4 class="mb-1">Primary Recommendation</h4>
            <h5 class="mb-1">{{ $topRecommendedArea->name }}</h5>
            <p class="mb-1">{{ $topRecommendedArea->address }}</p>
            <p class="mb-1">
                Capacity: {{ $topRecommendedArea->current_occupancy }}/{{ $topRecommendedArea->capacity }} 
                Allocated: {{ $topAllocated }}
            </p>
            <!-- Disaster Type removed from evacuation areas; no longer shown -->
            <p class="mb-0">Final Score: {{ number_format($topRecommendedArea->final_score, 2) }}/10</p>
        </div>

        @if(!empty($alternateAreas))
            <div class="p-3">
                <h4 class="mb-2">Alternate Areas</h4>
                @foreach($alternateAreas as $index => $alt)
                    <div class="mb-2" style="border-bottom: 1px dashed #ccc; padding-bottom: 5px;">
                        <h5 class="mb-1">{{ $alt['area']->name }}</h5>
                        <p class="mb-1">{{ $alt['area']->address }}</p>
                        <p class="mb-1">
                            Capacity: {{ $alt['area']->current_occupancy }}/{{ $alt['area']->capacity }} 
                            (Allocated: {{ $alt['allocated'] }})
                        </p>
                        <p class="mb-0">Final Score: {{ number_format($alt['area']->final_score, 2) }}/10</p>
                    </div>
                @endforeach
            </div>
        @endif
    @else
        <p class="p-3 text-muted">No recommended areas available.</p>
    @endif
</div>




@endsection

@section('scripts')
<script>
    // Disaster Type Chart
    const disasterTypeCtx = document.getElementById('disasterTypeChart').getContext('2d');
    const disasterTypeData = {!! json_encode($disasterTypeStats->pluck('disaster_type')->map(fn($type) => ucfirst($type))) !!};
    const disasterTypeCount = {!! json_encode($disasterTypeStats->pluck('count')) !!};
    
    const disasterTypeChart = new Chart(disasterTypeCtx, {
        type: 'doughnut',
        data: {
            labels: disasterTypeData.length > 0 ? disasterTypeData : ['No Data'],
            datasets: [{
                data: disasterTypeCount.length > 0 ? disasterTypeCount : [1],
                backgroundColor: disasterTypeCount.length > 0 ? [
                    '#667eea',
                    '#764ba2',
                    '#f093fb',
                    '#4facfe'
                ] : ['#e0e0e0']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    display: disasterTypeCount.length > 0
                }
            }
        }
    });

    // Severity Chart
    const severityCtx = document.getElementById('severityChart').getContext('2d');
    const severityData = {!! json_encode($severityStats->pluck('severity')->map(fn($sev) => ucfirst($sev))) !!};
    const severityCount = {!! json_encode($severityStats->pluck('count')) !!};
    
    const severityChart = new Chart(severityCtx, {
        type: 'pie',
        data: {
            labels: severityData.length > 0 ? severityData : ['No Data'],
            datasets: [{
                data: severityCount.length > 0 ? severityCount : [1],
                backgroundColor: severityCount.length > 0 ? [
                    '#28a745',
                    '#ffc107',
                    '#fd7e14',
                    '#dc3545'
                ] : ['#e0e0e0']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    display: severityCount.length > 0
                }
            }
        }
    });

    // Occupancy Chart
    const occupancyCtx = document.getElementById('occupancyChart').getContext('2d');
    const areaNames = {!! json_encode($evacuationAreas->pluck('name')) !!};
    const areaOccupancy = {!! json_encode($evacuationAreas->pluck('current_occupancy')) !!};
    const areaCapacity = {!! json_encode($evacuationAreas->pluck('capacity')) !!};
    
    const occupancyChart = new Chart(occupancyCtx, {
        type: 'bar',
        data: {
            labels: areaNames.length > 0 ? areaNames : ['No Evacuation Areas'],
            datasets: [{
                label: 'Current Occupancy',
                data: areaOccupancy.length > 0 ? areaOccupancy : [0],
                backgroundColor: '#667eea'
            }, {
                label: 'Capacity',
                data: areaCapacity.length > 0 ? areaCapacity : [0],
                backgroundColor: '#e0e0e0'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    position: 'top'
                }
            }
        }
    });
    
const forecastLabels = @json(array_column($forecastData,'date'));
const actualData = @json(array_column($forecastData,'actual'));
const forecastValues = @json(array_column($forecastData,'forecast'));

// Mark points with no actual as "future" for styling
const pointBackgrounds = actualData.map(val => val === null ? '#ff6b6b' : '#667eea'); // red for forecast only

const forecastCtx = document.getElementById('forecastChart').getContext('2d');
new Chart(forecastCtx, {
    type: 'line',
    data: {
        labels: forecastLabels,
        datasets: [
            {
                label: 'Actual',
                data: actualData,
                borderColor: '#28a745',
                fill: false,
                tension: 0.2,
                spanGaps: true, // allow breaks for null values
                pointBackgroundColor: actualData.map(val => val === null ? '#ff6b6b' : '#28a745'), // red for missing
            },
            {
                label: 'Forecast',
                data: forecastValues,
                borderColor: '#667eea',
                fill: false,
                tension: 0.2,
                pointBackgroundColor: pointBackgrounds, // red for Nov 26
                pointRadius: forecastValues.map((val, i) => actualData[i] === null ? 6 : 3), // bigger point for forecast only
                borderDash: actualData.map(val => val === null ? [5,5] : []), // dashed line for forecast-only points
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'top' }
        }
    }
});

</script>
@endsection