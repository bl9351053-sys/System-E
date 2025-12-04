<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Show the dashboard view. Heavy lifting is delegated to DashboardService.
     */
    public function index(Request $request)
    {
        // Optionally accept user location via query or use defaults inside service
        $latitude = $request->query('lat');
        $longitude = $request->query('lng');

        $data = $this->dashboardService->getDashboardData($latitude, $longitude);

        return view('dashboard', $data);
    }

    /**
     * API: Return the dashboard data as JSON for the residents app
     */
    public function api(Request $request)
    {
        $latitude = $request->query('lat');
        $longitude = $request->query('lng');

        $data = $this->dashboardService->getDashboardData($latitude, $longitude);

        return response()->json($data);
    }
}
