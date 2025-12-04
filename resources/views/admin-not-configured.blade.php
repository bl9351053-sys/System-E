@extends('layouts.app')

@section('title', 'Admin Not Configured')

@section('content')
    <div class="card" style="max-width:900px;margin:4rem auto;text-align:center;padding:3rem;">
        <h1 class="card-header">Admin App Not Configured</h1>
        <p style="margin:1rem 0;color:#444;">The admin application isn't configured for this installation.</p>

        <div style="margin-top:1rem;">
            <div style="color:#a94442;margin-bottom:1rem;">Please set <code>ADMIN_APP_URL</code> in your <code>.env</code> file to the admin application's URL, then restart the server.</div>
            <div>Example: <code>ADMIN_APP_URL=http://127.0.0.1:8001</code></div>
        </div>

        <div style="margin-top:2rem;">
            <a href="{{ route('choose-role') }}" class="btn btn-primary">Back to choose</a>
        </div>
    </div>
@endsection
