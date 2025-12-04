@extends('layouts.app')

@section('title', 'Choose Role - System-E')

@section('content')
    <div class="card" style="max-width:900px;margin:4rem auto;text-align:center;padding:2.5rem;">
        <h1 class="card-header">Choose role</h1>
        <p style="margin:0.6rem 0;color:#444;">Proceed as Resident or Admin</p>

        <div style="display:flex;gap:24px;justify-content:center;margin-top:2rem;">
            <a href="{{ route('choose.redirect', 'resident') }}" class="btn btn-primary" style="min-width:240px;padding:1.25rem 1.5rem;font-size:1.05rem;">Proceed as Resident</a>

            <!-- Server-side redirect will detect admin availability -->
            <a href="{{ route('choose.redirect', 'admin') }}" class="btn btn-secondary" style="min-width:240px;padding:1.25rem 1.5rem;font-size:1.05rem;">Proceed as Admin</a>
        </div>

        <div style="margin-top:1.25rem;color:#666;font-size:0.95rem;">
            Admin: <code>{{ $adminUrl ?? 'Not configured' }}</code>
        </div>
        </div>
    </div>
@endsection
