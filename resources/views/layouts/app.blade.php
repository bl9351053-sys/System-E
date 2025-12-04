<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Evacuation Management System')</title>
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    

    @stack('styles')
    @yield('styles')
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <a href="{{ route('home') }}" class="navbar-brand">
                HANDA
            </a>
            <ul class="navbar-menu">
                <li><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a></li>
                <li><a href="{{ route('real-time-data.index') }}" class="{{ request()->routeIs('real-time-data.*') ? 'active' : '' }}">Real-Time Data</a></li>
                <li><a href="{{ route('evacuation-areas.index') }}" class="{{ request()->routeIs('evacuation-areas.*') ? 'active' : '' }}">Evacuation Areas</a></li>
                <li><a href="{{ route('disaster-updates.index') }}" class="{{ request()->routeIs('disaster-updates.*') ? 'active' : '' }}">Updates</a></li>
                <li><a href="{{ route('disaster-predictions.index') }}" class="{{ request()->routeIs('disaster-predictions.*') ? 'active' : '' }}">Predictions</a></li>
                <li><a href="{{ route('families.index') }}" class="{{ request()->routeIs('families.*') ? 'active' : '' }}">Families</a></li>
                <li><a href="{{ route('choose-role') }}" class="{{ request()->routeIs('choose-role') ? 'active' : '' }}">Sign in</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">
                ✓ {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                ✗ {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <!-- Leaflet Routing Machine -->
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />

    <script>
        // CSRF token setup for AJAX requests
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    </script>

    @yield('scripts')
</body>


<style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: #667eea;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .navbar-brand::before {
            content: "🛡️";
            font-size: 1.8rem;
        }

        .navbar-menu {
            display: flex;
            gap: 2rem;
            list-style: none;
        }

        .navbar-menu a {
            color: #333;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
            padding: 0.5rem 1rem;
            border-radius: 5px;
        }

        .navbar-menu a:hover,
        .navbar-menu a.active {
            color: #667eea;
            background: rgba(102, 126, 234, 0.1);
        }

        .container {
            max-width: 1600px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }

        .card-header {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #667eea;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-warning {
            background: #ffc107;
            color: #333;
        }

        .btn-warning:hover {
            background: #e0a800;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .table th,
        .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        .table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        .table tbody tr:hover {
            background: #f8f9fa;
        }

        .badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        .badge-critical {
            background: #dc3545;
            color: white;
        }

        #map {
            height: 500px;
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-icon {
            font-size: 3rem;
        }

        .stat-info h3 {
            font-size: 2rem;
            color: #667eea;
            margin-bottom: 0.25rem;
        }

        .stat-info p {
            color: #666;
            font-size: 0.9rem;
        }

        /* Pagination Styles */
        .pagination {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            align-items: center;
            list-style: none;
        }

        .pagination .page-item {
            display: inline-block;
        }

        .pagination .page-link {
            padding: 0.5rem 0.75rem;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            color: #667eea;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s;
            display: inline-block;
        }

        .pagination .page-link svg {
            width: 16px;
            height: 16px;
        }

        .pagination .page-link:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .pagination .page-item.active .page-link {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .pagination .page-item.disabled .page-link {
            color: #999;
            cursor: not-allowed;
            opacity: 0.5;
        }

        .pagination .page-item.disabled .page-link:hover {
            background: transparent;
            color: #999;
            border-color: #e0e0e0;
        }

        /* Hide pagination SVG icons */
        nav[role="navigation"] svg {
            display: none !important;
        }

        /* Style pagination links */
        nav[role="navigation"] a,
        nav[role="navigation"] span {
            padding: 0.5rem 1rem;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-block;
            margin: 0 0.25rem;
        }

        nav[role="navigation"] a {
            color: #667eea;
            transition: all 0.3s;
        }

        nav[role="navigation"] a:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        nav[role="navigation"] span[aria-current="page"] {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        nav[role="navigation"] span[aria-disabled="true"] {
            color: #999;
            cursor: not-allowed;
            opacity: 0.5;
        }

        @media (max-width: 768px) {
            .navbar-menu {
                flex-direction: column;
                gap: 0.5rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>


</html>
