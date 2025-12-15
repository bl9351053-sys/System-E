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

    
    public function index(Request $request)
    {
        $latitude = $request->query('lat');
        $longitude = $request->query('lng');

        $data = $this->dashboardService->getDashboardData($latitude, $longitude);

        return view('dashboard', $data);
    }

    
    public function api(Request $request)
    {
        $latitude = $request->query('lat');
        $longitude = $request->query('lng');

        $data = $this->dashboardService->getDashboardData($latitude, $longitude);

        return response()->json($data);
    }
}
