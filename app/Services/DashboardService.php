<?php

namespace App\Services;

use App\Models\EvacuationArea;
use App\Models\Family;
use App\Models\DisasterUpdate;
use App\Models\DisasterPrediction;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    protected ForecastService $forecastService;
    protected AllocationService $allocationService;

    public function __construct(ForecastService $forecastService, AllocationService $allocationService)
    {
        $this->forecastService = $forecastService;
        $this->allocationService = $allocationService;
    }

    /**
     * Build an array of dashboard data used by web view and API.
     *
     * @param float|null $latitude
     * @param float|null $longitude
     * @return array
     */
    public function getDashboardData(float $latitude = null, float $longitude = null): array
    {
        $latitude = $latitude ?? 14.5995;
        $longitude = $longitude ?? 120.9842;

        $totalEvacuationAreas = EvacuationArea::count();
        $totalFamilies = Family::where('status', 'evacuated')->count();
        $totalPeople = Family::where('status', 'evacuated')->sum('total_members');
        $availableAreas = EvacuationArea::where('status', 'available')->count();
        $fullAreas = EvacuationArea::where('status', 'full')->count();

        $recentUpdates = DisasterUpdate::orderBy('issued_at', 'desc')->take(5)->get();

        $activePredictions = DisasterPrediction::where('risk_level', '>=', 5)
            ->orderBy('risk_level', 'desc')
            ->get();

        $evacuationAreas = EvacuationArea::all();

        $disasterTypeStats = DisasterUpdate::select('disaster_type', DB::raw('count(*) as count'))
            ->groupBy('disaster_type')
            ->get();

        $severityStats = DisasterUpdate::select('severity', DB::raw('count(*) as count'))
            ->groupBy('severity')
            ->get();

        $occupancyTrend = Family::select(
                DB::raw('DATE(checked_in_at) as date'),
                DB::raw('SUM(total_members) as total')
            )
            ->whereNotNull('checked_in_at')
            ->where('checked_in_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $forecastData = $this->forecastService->getForecastData();
        $forecastedEvacuees = $this->forecastService->getLatestForecastedEvacuees($forecastData);

        $recommendedAreas = $this->allocationService->recommendAreas($latitude, $longitude, $activePredictions);

        $allocationResult = $this->allocationService->allocate($recommendedAreas, $forecastedEvacuees);

        return [
            'totalEvacuationAreas' => $totalEvacuationAreas,
            'totalFamilies' => $totalFamilies,
            'totalPeople' => $totalPeople,
            'availableAreas' => $availableAreas,
            'fullAreas' => $fullAreas,
            'recentUpdates' => $recentUpdates,
            'activePredictions' => $activePredictions,
            'evacuationAreas' => $evacuationAreas,
            'disasterTypeStats' => $disasterTypeStats,
            'severityStats' => $severityStats,
            'occupancyTrend' => $occupancyTrend,
            'forecastData' => $forecastData,
            'recommendedAreas' => $recommendedAreas,
            'topAllocated' => $allocationResult['topAllocated'] ?? 0,
            'topRecommendedArea' => $allocationResult['topRecommendedArea'] ?? null,
            'alternateAreas' => $allocationResult['alternateAreas'] ?? [],
        ];
    }
}
