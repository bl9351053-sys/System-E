@extends('layouts.app')

@section('title', 'Disaster Updates')

@section('content')
<div class="card">
    <div class="flex-between mb-15">
        <div class="card-header card-header-no-border">🚨 Disaster Updates</div>
        <button onclick="syncUpdates()" class="btn btn-success">🔄 Sync from PAGASA/PhiVolcs/NDRRMC</button>
    </div>
    
    <div class="info-box warning mb-15">
        <strong>📡 Official Data Sources:</strong> Disaster updates are automatically synced from official Philippine government agencies:
        <strong>PAGASA</strong> (weather, typhoons, floods), <strong>PhiVolcs</strong> (earthquakes, volcanoes, tsunamis), and <strong>NDRRMC</strong> (disaster management).
        Click "Sync" to fetch the latest updates.
    </div>
    
    @if($updates->count() > 0)
        @foreach($updates as $update)
            <div class="update-card {{ $update->severity == 'critical' ? 'critical' : ($update->severity == 'high' ? 'high' : '') }}">
                <div class="flex-start mb-1">
                    <div class="flex-1">
                        <div class="flex-gap mb-05">
                            <h3 class="section-title">{{ $update->title }}</h3>
                            <span class="badge badge-{{ $update->severity == 'critical' ? 'critical' : ($update->severity == 'high' ? 'danger' : ($update->severity == 'moderate' ? 'warning' : 'info')) }}">
                                {{ ucfirst($update->severity) }}
                            </span>
                            <span class="badge badge-info">{{ ucfirst($update->disaster_type) }}</span>
                        </div>
                        <small class="text-light-muted">{{ $update->source }} • {{ $update->issued_at->format('M d, Y h:i A') }} ({{ $update->issued_at->diffForHumans() }})</small>
                    </div>
                    <a href="{{ route('disaster-updates.show', $update) }}" class="btn btn-primary btn-small">View Details</a>
                </div>
                <p class="text-muted mb-0">{{ $update->description }}</p>
            </div>
        @endforeach
        
        <div class="mt-15">
            {{ $updates->links() }}
        </div>
    @else
        <p class="text-muted p-1">No disaster updates available.</p>
    @endif
</div>
@endsection

@section('scripts')
<script>
    // Sync disaster updates from PAGASA/PhiVolcs/NDRRMC
    async function syncUpdates() {
        const button = event.target;
        const originalText = button.textContent;
        button.disabled = true;
        button.textContent = '⏳ Syncing from PAGASA/PhiVolcs/NDRRMC...';

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
                alert('✓ Successfully synced ' + result.synced_count + ' updates from PAGASA, PhiVolcs, and NDRRMC!\n\n' + result.message);
                location.reload();
            } else {
                alert('✗ Error: ' + result.message);
            }
        } catch (error) {
            alert('Error syncing updates. Please try again.');
            console.error(error);
        } finally {
            button.disabled = false;
            button.textContent = originalText;
        }
    }
</script>
@endsection
