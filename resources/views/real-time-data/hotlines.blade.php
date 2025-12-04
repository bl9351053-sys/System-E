@extends('layouts.app')

@section('title', 'Emergency Hotlines')

@section('content')
<div class="card">
    <div class="card-header">📞 Emergency Hotlines Directory</div>
    
    <div style="background: #fff5f5; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border-left: 4px solid #dc3545;">
        <strong>🚨 IN CASE OF EMERGENCY:</strong> Dial <strong style="font-size: 1.5rem; color: #dc3545;">911</strong> for immediate assistance
    </div>
    
    @foreach($hotlines as $key => $hotline)
        <div style="padding: 1.5rem; border: 2px solid #e0e0e0; border-radius: 12px; margin-bottom: 1rem;">
            <h3 style="margin-bottom: 1rem; color: #333;">{{ $hotline['name'] }}</h3>
            
            <div style="margin-bottom: 1rem;">
                <strong style="color: #666;">Contact Numbers:</strong>
                @foreach($hotline['numbers'] as $number)
                    <div style="margin: 0.5rem 0;">
                        <a href="tel:{{ str_replace(['(', ')', ' ', '-'], '', $number) }}" style="color: #dc3545; font-weight: bold; font-size: 1.2rem; text-decoration: none;">
                            📞 {{ $number }}
                        </a>
                    </div>
                @endforeach
            </div>
            
            @if(isset($hotline['email']))
                <div style="margin-bottom: 0.5rem;">
                    <strong style="color: #666;">Email:</strong>
                    <a href="mailto:{{ $hotline['email'] }}" style="color: #667eea; text-decoration: none;">
                        ✉️ {{ $hotline['email'] }}
                    </a>
                </div>
            @endif
        </div>
    @endforeach
</div>

<div class="card" style="background: #e7f3ff;">
    <div class="card-header">ℹ️ Important Reminders</div>
    <ul style="color: #666; line-height: 1.8;">
        <li>Keep these numbers saved in your phone</li>
        <li>Stay calm when calling emergency services</li>
        <li>Provide clear information about your location and situation</li>
        <li>Follow instructions from emergency responders</li>
        <li>Do not hang up until told to do so</li>
    </ul>
</div>

<div style="margin-top: 1.5rem; text-align: center;">
    <a href="{{ route('real-time-data.index') }}" class="btn btn-primary">← Back to Real-Time Data</a>
    <a href="{{ route('dashboard') }}" class="btn btn-secondary">Go to Dashboard</a>
</div>
@endsection
