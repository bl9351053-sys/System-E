@extends('layouts.app')

@section('title', 'Real-Time Data - PAGASA/PhiVolcs/NDRRMC')

@section('content')
<div class="card">
    <div class="flex-between mb-15">
        <div class="card-header card-header-no-border">📡 Real-Time Disaster Data</div>
        <button onclick="syncData()" class="btn btn-success">🔄 Sync Latest Data</button>
    </div>

    <div class="warning-card mb-15">
        <strong>⚠️ Data Sources:</strong> This system integrates real-time data from official Philippine government agencies:
        <strong>PAGASA</strong> (weather), <strong>PhiVolcs</strong> (earthquakes/volcanoes), and <strong>NDRRMC</strong> (disaster management).
    </div>
</div>

@if(count($alerts) > 0)
<div class="card alert-card">
    <div class="card-header alert-header">🚨 ACTIVE ALERTS</div>
    @foreach($alerts as $alert)
        <div class="update-item" style="border-bottom: 1px solid #f8d7da;">
            <div class="flex-between">
                <div>
                    <h4 class="mb-05" style="color: #721c24;">{{ $alert['message'] }}</h4>
                    <p class="text-muted" style="margin-bottom: 0.25rem;">Location: {{ $alert['location'] }}</p>
                    <small class="text-light-muted">{{ $alert['source'] }} • {{ date('M d, Y h:i A', $alert['timestamp']) }}</small>
                </div>
                <span class="badge badge-{{ $alert['severity'] == 'critical' ? 'critical' : ($alert['severity'] == 'high' ? 'danger' : 'warning') }}">
                    {{ ucfirst($alert['severity']) }}
                </span>
            </div>
        </div>
    @endforeach
</div>
@endif


<div class="card">
    <div class="card-header">🌤️ PAGASA - Weather Information</div>

    <div class="data-grid mb-15">
        @if($weather['temperature'])
            <div class="data-card">
                <div class="data-card-label">Temperature</div>
                <div class="data-card-value">{{ $weather['temperature'] }}°C</div>
            </div>
        @endif

        @if($weather['humidity'])
            <div class="data-card">
                <div class="data-card-label">Humidity</div>
                <div class="data-card-value">{{ $weather['humidity'] }}%</div>
            </div>
        @endif

        @if($weather['wind_speed'])
            <div class="data-card">
                <div class="data-card-label">Wind Speed</div>
                <div class="data-card-value">{{ $weather['wind_speed'] }} m/s</div>
            </div>
        @endif

        @if($weather['pressure'])
            <div class="data-card">
                <div class="data-card-label">Pressure</div>
                <div class="data-card-value">{{ $weather['pressure'] }} hPa</div>
            </div>
        @endif
    </div>

    @if($weather['description'])
        <p class="text-muted mb-05"><strong>Conditions:</strong> {{ ucfirst($weather['description']) }}</p>
    @endif
    <small class="text-light-muted">Last updated: {{ $weather['updated_at']->diffForHumans() }}</small>
</div>


@if(count($floodWarnings) > 0)
<div class="card warning-card">
    <div class="card-header">💧 PAGASA - Flood Warnings</div>
    @foreach($floodWarnings as $warning)
        <div class="warning-item">
            <h4 class="warning-title">{{ $warning['location'] }}</h4>
            <p class="text-muted">{{ $warning['message'] }}</p>
            <span class="badge badge-{{ $warning['severity'] == 'high' ? 'danger' : 'warning' }}">
                {{ ucfirst($warning['severity']) }} Risk
            </span>
        </div>
    @endforeach
</div>
@endif


<div class="card">
    <div class="card-header">🌍 PhiVolcs - Recent Earthquakes</div>

    @if(count($earthquakes) > 0)
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Magnitude</th>
                        <th>Location</th>
                        <th>Depth</th>
                        <th>Time</th>
                        <th>Significance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($earthquakes as $quake)
                        <tr class="{{ $quake['magnitude'] >= 5.0 ? 'quake-row-critical' : '' }}">
                            <td><strong class="magnitude-text {{ $quake['magnitude'] >= 6.0 ? 'magnitude-critical' : ($quake['magnitude'] >= 5.0 ? 'magnitude-high' : 'magnitude-normal') }}">{{ $quake['magnitude'] }}</strong></td>
                            <td>{{ $quake['location'] }}</td>
                            <td>{{ $quake['depth'] }} km</td>
                            <td>{{ date('M d, h:i A', strtotime($quake['timestamp'])) }}</td>
                            <td>
                                <span class="badge badge-{{ $quake['significance'] == 'critical' ? 'critical' : ($quake['significance'] == 'high' ? 'danger' : ($quake['significance'] == 'moderate' ? 'warning' : 'info')) }}">
                                    {{ ucfirst($quake['significance']) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-muted p-1">No significant earthquakes in the past 7 days.</p>
    @endif

    <small class="text-light-muted" style="padding: 0 1rem;">Source: PhiVolcs/USGS Earthquake Monitoring</small>
</div>


@if($tsunami['active'])
<div class="card alert-card">
    <div class="card-header alert-header">🌊 PhiVolcs - TSUNAMI ADVISORY</div>
    <div class="p-1">
        <h3 class="mb-1" style="color: #721c24;">{{ strtoupper($tsunami['message']) }}</h3>
        <span class="badge badge-critical" style="font-size: 1.1rem;">{{ strtoupper($tsunami['level']) }} ALERT</span>
        <p class="text-muted mt-1">Issued: {{ $tsunami['issued_at']?->format('M d, Y h:i A') ?? 'N/A' }}</p>
    </div>
</div>
@endif


<div class="card">
    <div class="card-header">🌋 PhiVolcs - Volcano Monitoring</div>

    <div class="data-grid-large">
        @foreach($volcanoes as $volcano)
            <div class="volcano-card {{ $volcano['alert_level'] >= 2 ? 'alert' : 'normal' }}">
                <h4 class="mb-05">{{ $volcano['name'] }}</h4>
                <p class="text-muted text-small mb-05">{{ $volcano['description'] }}</p>
                <div class="mt-1">
                    <span class="badge badge-{{ $volcano['alert_level'] >= 3 ? 'critical' : ($volcano['alert_level'] >= 2 ? 'danger' : ($volcano['alert_level'] == 1 ? 'warning' : 'success')) }}">
                        Alert Level {{ $volcano['alert_level'] }}
                    </span>
                    <span class="badge badge-info">{{ $volcano['status'] }}</span>
                </div>
                <small class="text-light-muted" style="display: block; margin-top: 0.5rem;">Last eruption: {{ $volcano['last_eruption'] }}</small>
            </div>
        @endforeach
    </div>
</div>


@if($situationReport)
<div class="card">
    <div class="card-header">📋 NDRRMC - Situation Report</div>

    <div class="info-card-blue mb-1">
        <p class="mb-05"><strong>Report Number:</strong> {{ $situationReport['report_number'] }}</p>
        <p class="mb-0"><strong>Reporting Period:</strong> {{ $situationReport['reporting_period'] }}</p>
    </div>

    <h4 class="mb-1">Evacuation Centers</h4>
    <div class="data-grid mb-15">
        <div class="data-card">
            <div class="data-card-label">Total Centers</div>
            <div class="data-card-value">{{ $situationReport['evacuation_centers']['total'] }}</div>
        </div>
        <div class="data-card">
            <div class="data-card-label">Occupied</div>
            <div class="data-card-value">{{ $situationReport['evacuation_centers']['occupied'] }}</div>
        </div>
        <div class="data-card">
            <div class="data-card-label">Families</div>
            <div class="data-card-value">{{ $situationReport['evacuation_centers']['families'] }}</div>
        </div>
        <div class="data-card">
            <div class="data-card-label">Persons</div>
            <div class="data-card-value">{{ $situationReport['evacuation_centers']['persons'] }}</div>
        </div>
    </div>

    <small class="text-light-muted">Last updated: {{ $situationReport['updated_at']->diffForHumans() }}</small>
</div>
@endif


<div class="card">
    <div class="card-header">📞 Emergency Hotlines</div>

    <div class="data-grid-large">
        @foreach($emergencyHotlines as $key => $hotline)
            <div class="hotline-card">
                <h4 class="mb-05 text-dark">{{ $hotline['name'] }}</h4>
                @foreach($hotline['numbers'] as $number)
                    <p class="hotline-number">📞 {{ $number }}</p>
                @endforeach
                @if(isset($hotline['email']))
                    <p class="hotline-email">✉️ {{ $hotline['email'] }}</p>
                @endif
            </div>
        @endforeach
    </div>

    <a href="{{ route('emergency-hotlines') }}" class="btn btn-primary mt-1 w-100">View Complete Hotline Directory</a>
</div>

<!-- Data Sources -->
<div class="card" style="background: #f8f9fa;">
    <div class="card-header">ℹ️ About the Data</div>
    <p class="text-muted mb-1">
        This system integrates real-time data from official Philippine government agencies to provide accurate and reliable disaster information:
    </p>
    <ul class="text-muted" style="line-height: 1.8;">
        <li><strong>PAGASA (Philippine Atmospheric, Geophysical and Astronomical Services Administration)</strong> - Weather forecasts, tropical cyclones, rainfall data, and flood warnings</li>
        <li><strong>PhiVolcs (Philippine Institute of Volcanology and Seismology)</strong> - Earthquake monitoring, volcano status, tsunami advisories, and fault line information</li>
        <li><strong>NDRRMC (National Disaster Risk Reduction and Management Council)</strong> - Situation reports, evacuation statistics, and disaster response coordination</li>
        <li><strong>USGS (United States Geological Survey)</strong> - Supplementary earthquake data for the Philippine region</li>
    </ul>
    <p class="text-muted mt-1">
        <strong>Note:</strong> Data is cached for 5-30 minutes to ensure system performance. Click "Sync Latest Data" to fetch the most recent information.
    </p>
</div>
@endsection

@section('scripts')
<script>
    async function syncData() {
        const button = event.target;
        button.disabled = true;
        button.textContent = '⏳ Syncing...';

        try {
            const response = await fetch('{{ route("real-time-data.sync") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const result = await response.json();

            if (result.success) {
                alert('✓ ' + result.message);
                location.reload();
            } else {
                alert('✗ ' + result.message);
            }
        } catch (error) {
            alert('Error syncing data. Please try again.');
            console.error(error);
        } finally {
            button.disabled = false;
            button.textContent = '🔄 Sync Latest Data';
        }
    }

    // Auto-refresh every 5 minutes
    setTimeout(() => {
        location.reload();
    }, 300000);
</script>
@endsection
