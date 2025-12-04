<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PagasaApiService;
use App\Services\PhivolcsApiService;
use App\Services\NdrmcApiService;
use App\Models\DisasterUpdate;
use App\Models\DisasterPrediction;

class RealTimeDataController extends Controller
{
    protected $pagasaService;
    protected $phivolcsService;
    protected $ndrmcService;

    public function __construct(
        PagasaApiService $pagasaService,
        PhivolcsApiService $phivolcsService,
        NdrmcApiService $ndrmcService
    ) {
        $this->pagasaService = $pagasaService;
        $this->phivolcsService = $phivolcsService;
        $this->ndrmcService = $ndrmcService;
    }

    /**
     * Display real-time data dashboard
     */
    public function index()
    {
        $weather = $this->pagasaService->getCurrentWeather();
        $cyclones = $this->pagasaService->getTropicalCyclones();
        $rainfall = $this->pagasaService->getRainfallData();
        $floodWarnings = $this->pagasaService->getFloodWarnings();
        
        $earthquakes = $this->phivolcsService->getRecentEarthquakes(10);
        $volcanoes = $this->phivolcsService->getVolcanoStatus();
        $tsunami = $this->phivolcsService->getTsunamiAdvisory();
        
        $situationReport = $this->ndrmcService->getSituationReport();
        $emergencyHotlines = $this->ndrmcService->getEmergencyHotlines();
        $alerts = $this->ndrmcService->getRealTimeAlerts();

        return view('real-time-data.index', compact(
            'weather',
            'cyclones',
            'rainfall',
            'floodWarnings',
            'earthquakes',
            'volcanoes',
            'tsunami',
            'situationReport',
            'emergencyHotlines',
            'alerts'
        ));
    }

    /**
     * Sync real-time data to database - Creates disaster updates from PAGASA, PhiVolcs, and NDRRMC
     */
    public function syncData(Request $request)
{
    $token = $request->bearerToken();
    if ($token !== env('RESIDENT_API_TOKEN')) {
        return response()->json(['success'=>false,'message'=>'Unauthorized'], 401);
    }


    try {
        $synced = 0;

        $synced += $this->syncEarthquakeUpdates();
        $synced += $this->syncFloodUpdates();
        $synced += $this->syncTyphoonUpdates();
        $synced += $this->syncVolcanoUpdates();
        $synced += $this->syncTsunamiUpdates();
        $synced += $this->syncWeatherAdvisories();

        $this->createPredictionsFromData();

        return response()->json([
            'success' => true,
            'message' => "Successfully synced {$synced} updates from PAGASA, PhiVolcs, and NDRRMC",
            'synced_count' => $synced,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error syncing data: ' . $e->getMessage(),
        ], 500);
    }
}


    /**
     * Sync earthquake updates from PhiVolcs/USGS
     */
    private function syncEarthquakeUpdates()
    {
        $synced = 0;
        $earthquakes = $this->phivolcsService->getRecentEarthquakes(10);
        
        foreach ($earthquakes as $quake) {
            if ($quake['magnitude'] >= 3.5) {
                $exists = DisasterUpdate::where('disaster_type', 'earthquake')
                    ->where('title', 'like', "%Magnitude {$quake['magnitude']}%")
                    ->where('latitude', $quake['latitude'])
                    ->where('issued_at', '>=', now()->subHours(24))
                    ->exists();

                if (!$exists) {
                    $severity = $this->calculateEarthquakeSeverity($quake['magnitude']);
                    
                    DisasterUpdate::create([
                        'disaster_type' => 'earthquake',
                        'title' => "M{$quake['magnitude']} Earthquake - {$quake['location']}",
                        'description' => "PhiVolcs/USGS Report: A magnitude {$quake['magnitude']} earthquake was recorded at {$quake['location']} at depth of {$quake['depth']}km. " . 
                                       ($quake['magnitude'] >= 5.0 ? "This is a significant earthquake. " : "") .
                                       ($quake['tsunami_warning'] ? "⚠️ Tsunami warning issued. Coastal areas should evacuate immediately." : "No tsunami threat detected.") .
                                       ($quake['felt_reports'] > 0 ? " Felt by {$quake['felt_reports']} people." : ""),
                        'severity' => $severity,
                        'source' => 'PhiVolcs/USGS',
                        'latitude' => $quake['latitude'],
                        'longitude' => $quake['longitude'],
                        'issued_at' => $quake['timestamp'],
                    ]);
                    $synced++;
                }
            }
        }
        
        return $synced;
    }

    /**
     * Sync flood updates from PAGASA
     */
    private function syncFloodUpdates()
    {
        $synced = 0;
        $floodWarnings = $this->pagasaService->getFloodWarnings();
        
        foreach ($floodWarnings as $warning) {
            $exists = DisasterUpdate::where('disaster_type', 'flood')
                ->where('title', 'like', "%{$warning['location']}%")
                ->where('issued_at', '>=', now()->subHours(6))
                ->exists();

            if (!$exists) {
                DisasterUpdate::create([
                    'disaster_type' => 'flood',
                    'title' => "Flood Warning - {$warning['location']}",
                    'description' => "PAGASA Advisory: {$warning['message']} Heavy rainfall has been detected in the area. Residents in low-lying areas and near rivers should be on high alert. Monitor local advisories and prepare to evacuate if necessary.",
                    'severity' => $warning['severity'],
                    'source' => 'PAGASA',
                    'latitude' => 14.5995,
                    'longitude' => 120.9842,
                    'issued_at' => date('Y-m-d H:i:s', $warning['timestamp']),
                ]);
                $synced++;
            }
        }
        
        return $synced;
    }

    /**
     * Sync typhoon updates from PAGASA
     */
    private function syncTyphoonUpdates()
    {
        $synced = 0;
        $cyclones = $this->pagasaService->getTropicalCyclones();
        
        foreach ($cyclones as $cyclone) {
            if (!isset($cyclone['name'])) continue;
            
            $exists = DisasterUpdate::where('disaster_type', 'typhoon')
                ->where('title', 'like', "%{$cyclone['name']}%")
                ->where('issued_at', '>=', now()->subHours(12))
                ->exists();

            if (!$exists) {
                $windSpeedValue = isset($cyclone['wind_speed']) ? $cyclone['wind_speed'] : 0;
                $severity = $this->calculateTyphoonSeverity($windSpeedValue);
                
                $category = isset($cyclone['category']) ? $cyclone['category'] : 'Tropical Cyclone';
                $track = isset($cyclone['track']) ? $cyclone['track'] : 'towards the Philippines';
                $landfall = isset($cyclone['expected_landfall']) ? $cyclone['expected_landfall'] : 'To be determined';
                $windSpeed = isset($cyclone['wind_speed']) ? $cyclone['wind_speed'] : 'N/A';
                $lat = isset($cyclone['latitude']) ? $cyclone['latitude'] : 14.5995;
                $lon = isset($cyclone['longitude']) ? $cyclone['longitude'] : 120.9842;
                
                DisasterUpdate::create([
                    'disaster_type' => 'typhoon',
                    'title' => "Typhoon {$cyclone['name']} - {$category}",
                    'description' => "PAGASA Tropical Cyclone Bulletin: Typhoon {$cyclone['name']} is currently tracking {$track}. " .
                                   "Maximum sustained winds: {$windSpeed}kph. " .
                                   "Expected landfall: {$landfall}. " .
                                   "Residents in the projected path should prepare for heavy rainfall, strong winds, and possible flooding. Follow PAGASA updates closely.",
                    'severity' => $severity,
                    'source' => 'PAGASA',
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'issued_at' => now(),
                ]);
                $synced++;
            }
        }
        
        return $synced;
    }

    /**
     * Sync volcano updates from PhiVolcs
     */
    private function syncVolcanoUpdates()
    {
        $synced = 0;
        $volcanoes = $this->phivolcsService->getVolcanoStatus();
        
        foreach ($volcanoes as $volcano) {
            if ($volcano['alert_level'] >= 1) {
                $exists = DisasterUpdate::where('disaster_type', 'earthquake')
                    ->where('title', 'like', "%{$volcano['name']}%")
                    ->where('issued_at', '>=', now()->subDays(1))
                    ->exists();

                if (!$exists) {
                    $severity = $this->calculateVolcanoSeverity($volcano['alert_level']);
                    
                    DisasterUpdate::create([
                        'disaster_type' => 'earthquake',
                        'title' => "{$volcano['name']} - Alert Level {$volcano['alert_level']}",
                        'description' => "PhiVolcs Volcano Bulletin: {$volcano['name']} is currently at Alert Level {$volcano['alert_level']} ({$volcano['status']}). " .
                                       "{$volcano['description']} " .
                                       "Last eruption: {$volcano['last_eruption']}. " .
                                       ($volcano['alert_level'] >= 2 ? "⚠️ Residents near the volcano should prepare for possible evacuation. " : "") .
                                       "Monitor PhiVolcs advisories for updates.",
                        'severity' => $severity,
                        'source' => 'PhiVolcs',
                        'latitude' => $volcano['latitude'],
                        'longitude' => $volcano['longitude'],
                        'issued_at' => now(),
                    ]);
                    $synced++;
                }
            }
        }
        
        return $synced;
    }

    /**
     * Sync tsunami updates from PhiVolcs
     */
    private function syncTsunamiUpdates()
    {
        $synced = 0;
        $tsunami = $this->phivolcsService->getTsunamiAdvisory();
        
        if ($tsunami['active']) {
            $exists = DisasterUpdate::where('disaster_type', 'earthquake')
                ->where('title', 'like', '%Tsunami%')
                ->where('issued_at', '>=', now()->subHours(6))
                ->exists();

            if (!$exists) {
                $tsunamiLat = isset($tsunami['earthquake']['latitude']) ? $tsunami['earthquake']['latitude'] : 14.5995;
                $tsunamiLon = isset($tsunami['earthquake']['longitude']) ? $tsunami['earthquake']['longitude'] : 120.9842;
                
                DisasterUpdate::create([
                    'disaster_type' => 'earthquake',
                    'title' => "⚠️ TSUNAMI ADVISORY - {$tsunami['level']} Alert",
                    'description' => "PhiVolcs Tsunami Warning: {$tsunami['message']} " .
                                   "A significant earthquake has triggered a tsunami warning. " .
                                   "Coastal residents should evacuate to higher ground immediately. " .
                                   "Stay away from beaches and coastal areas until the all-clear is given. " .
                                   "Follow local authorities' instructions.",
                    'severity' => $tsunami['level'] == 'critical' ? 'critical' : 'high',
                    'source' => 'PhiVolcs',
                    'latitude' => $tsunamiLat,
                    'longitude' => $tsunamiLon,
                    'issued_at' => $tsunami['issued_at'],
                ]);
                $synced++;
            }
        }
        
        return $synced;
    }

    /**
     * Sync weather advisories from PAGASA
     */
    private function syncWeatherAdvisories()
    {
        $synced = 0;
        $weather = $this->pagasaService->getCurrentWeather();
        $rainfall = $this->pagasaService->getRainfallData();
        
        // Check for extreme weather conditions
        $totalRainfall = array_sum(array_column($rainfall, 'rain'));
        $windSpeed = isset($weather['wind_speed']) ? $weather['wind_speed'] : 0;
        
        if ($totalRainfall > 80 || $windSpeed > 20) {
            $exists = DisasterUpdate::where('disaster_type', 'typhoon')
                ->where('title', 'like', '%Weather Advisory%')
                ->where('issued_at', '>=', now()->subHours(6))
                ->exists();

            if (!$exists) {
                $conditions = [];
                if ($totalRainfall > 80) $conditions[] = "Heavy rainfall ({$totalRainfall}mm)";
                $weatherWindSpeed = isset($weather['wind_speed']) ? $weather['wind_speed'] : 0;
                if ($weatherWindSpeed > 20) $conditions[] = "Strong winds ({$weatherWindSpeed}m/s)";
                
                DisasterUpdate::create([
                    'disaster_type' => 'typhoon',
                    'title' => "PAGASA Weather Advisory - Severe Weather Conditions",
                    'description' => "PAGASA Weather Update: " . implode(' and ', $conditions) . " detected in Metro Manila and surrounding areas. " .
                                   "Current conditions: {$weather['description']}. " .
                                   "Temperature: {$weather['temperature']}°C, Humidity: {$weather['humidity']}%. " .
                                   "Residents should stay indoors, secure loose objects, and avoid unnecessary travel. Monitor PAGASA updates.",
                    'severity' => $totalRainfall > 100 ? 'high' : 'moderate',
                    'source' => 'PAGASA',
                    'latitude' => 14.5995,
                    'longitude' => 120.9842,
                    'issued_at' => now(),
                ]);
                $synced++;
            }
        }
        
        return $synced;
    }

    /**
     * Calculate earthquake severity based on magnitude
     */
    private function calculateEarthquakeSeverity($magnitude)
    {
        if ($magnitude >= 7.0) return 'critical';
        if ($magnitude >= 6.0) return 'high';
        if ($magnitude >= 5.0) return 'moderate';
        return 'low';
    }

    /**
     * Calculate typhoon severity based on wind speed
     */
    private function calculateTyphoonSeverity($windSpeed)
    {
        if ($windSpeed >= 185) return 'critical'; // Super Typhoon
        if ($windSpeed >= 118) return 'high';     // Typhoon
        if ($windSpeed >= 62) return 'moderate';  // Tropical Storm
        return 'low';
    }

    /**
     * Calculate volcano severity based on alert level
     */
    private function calculateVolcanoSeverity($alertLevel)
    {
        if ($alertLevel >= 4) return 'critical';
        if ($alertLevel >= 3) return 'high';
        if ($alertLevel >= 2) return 'moderate';
        return 'low';
    }

    /**
     * Create predictions based on real-time data from PAGASA, PhiVolcs, and NDRRMC
     */
    private function createPredictionsFromData()
    {
        // 1. EARTHQUAKE PREDICTIONS from PhiVolcs fault line data
        $this->createEarthquakePredictions();
        
        // 2. FLOOD PREDICTIONS from PAGASA rainfall data
        $this->createFloodPredictions();
        
        // 3. TYPHOON PREDICTIONS from PAGASA cyclone tracking
        $this->createTyphoonPredictions();
        
        // 4. LANDSLIDE PREDICTIONS from combined PAGASA rainfall + PhiVolcs terrain data
        $this->createLandslidePredictions();
        
        // 5. VOLCANO PREDICTIONS from PhiVolcs volcano monitoring
        $this->createVolcanoPredictions();
    }

    /**
     * Create earthquake predictions from PhiVolcs fault line data
     */
    private function createEarthquakePredictions()
    {
        // Get fault lines near Metro Manila and surrounding areas
        $faultLines = $this->phivolcsService->getNearbyFaultLines(14.5995, 120.9842, 100);
        
        foreach ($faultLines as $fault) {
            $exists = DisasterPrediction::where('disaster_type', 'earthquake')
                ->where('location_name', 'like', "%{$fault['name']}%")
                ->where('predicted_at', '>=', now()->subDays(30))
                ->exists();

            if (!$exists) {
                DisasterPrediction::create([
                    'disaster_type' => 'earthquake',
                    'latitude' => $fault['latitude'],
                    'longitude' => $fault['longitude'],
                    'location_name' => $fault['name'] . ' Zone',
                    'risk_level' => $fault['risk_level'],
                    'predicted_recovery_days' => $this->calculateRecoveryDays('earthquake', $fault['risk_level']),
                    'prediction_factors' => "PhiVolcs Data: Active fault line - {$fault['name']}. Length: {$fault['length_km']}km. Type: {$fault['type']}. Last movement: {$fault['last_movement']}. Distance from Metro Manila: {$fault['distance_km']}km. High seismic risk area.",
                    'predicted_at' => now(),
                ]);
            }
        }

        // Check recent earthquake activity for aftershock predictions
        $earthquakes = $this->phivolcsService->getRecentEarthquakes(5);
        foreach ($earthquakes as $quake) {
            if ($quake['magnitude'] >= 5.0) {
                $exists = DisasterPrediction::where('disaster_type', 'earthquake')
                    ->where('location_name', 'like', "%Aftershock%")
                    ->where('latitude', $quake['latitude'])
                    ->where('predicted_at', '>=', now()->subDays(1))
                    ->exists();

                if (!$exists) {
                    DisasterPrediction::create([
                        'disaster_type' => 'earthquake',
                        'latitude' => $quake['latitude'],
                        'longitude' => $quake['longitude'],
                        'location_name' => 'Aftershock Zone - ' . $quake['location'],
                        'risk_level' => min(8, (int)$quake['magnitude']),
                        'predicted_recovery_days' => 7,
                        'prediction_factors' => "PhiVolcs/USGS Data: Recent M{$quake['magnitude']} earthquake detected. Aftershocks expected in the next 48-72 hours. Depth: {$quake['depth']}km. Source: {$quake['source']}.",
                        'predicted_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Create flood predictions from PAGASA rainfall data
     */
    private function createFloodPredictions()
    {
        $rainfall = $this->pagasaService->getRainfallData();
        $floodWarnings = $this->pagasaService->getFloodWarnings();
        
        $totalRainfall = 0;
        $heavyRainfallCount = 0;
        
        foreach ($rainfall as $data) {
            $totalRainfall += ($data['rain'] ?? 0);
            if (($data['rain'] ?? 0) > 15) {
                $heavyRainfallCount++;
            }
        }

        if ($heavyRainfallCount >= 2 || $totalRainfall > 50) {
            $riskLevel = $totalRainfall > 100 ? 9 : ($totalRainfall > 70 ? 8 : 7);
            
            $exists = DisasterPrediction::where('disaster_type', 'flood')
                ->where('location_name', 'like', '%Metro Manila%')
                ->where('predicted_at', '>=', now()->subHours(6))
                ->exists();

            if (!$exists) {
                DisasterPrediction::create([
                    'disaster_type' => 'flood',
                    'latitude' => 14.5995,
                    'longitude' => 120.9842,
                    'location_name' => 'Metro Manila Low-Lying Areas',
                    'risk_level' => $riskLevel,
                    'predicted_recovery_days' => $riskLevel >= 8 ? 5 : 3,
                    'prediction_factors' => "PAGASA Data: Heavy rainfall detected. Total: {$totalRainfall}mm in 24hrs. {$heavyRainfallCount} periods of intense rain. Poor drainage systems. Historical flooding patterns. " . (count($floodWarnings) > 0 ? "Active flood warnings issued." : ""),
                    'predicted_at' => now(),
                ]);
            }
        }
    }

    /**
     * Create typhoon predictions from PAGASA cyclone tracking
     */
    private function createTyphoonPredictions()
    {
        $cyclones = $this->pagasaService->getTropicalCyclones();
        
        foreach ($cyclones as $cyclone) {
            $exists = DisasterPrediction::where('disaster_type', 'typhoon')
                ->where('location_name', 'like', "%{$cyclone['name']}%")
                ->where('predicted_at', '>=', now()->subDays(3))
                ->exists();

            if (!$exists && isset($cyclone['name'])) {
                DisasterPrediction::create([
                    'disaster_type' => 'typhoon',
                    'latitude' => $cyclone['latitude'] ?? 14.5995,
                    'longitude' => $cyclone['longitude'] ?? 120.9842,
                    'location_name' => 'Typhoon ' . $cyclone['name'] . ' Path',
                    'risk_level' => $cyclone['risk_level'] ?? 8,
                    'predicted_recovery_days' => 10,
                    'prediction_factors' => "PAGASA Tropical Cyclone Bulletin: {$cyclone['name']}. Wind speed: {$cyclone['wind_speed']}kph. Category: {$cyclone['category']}. Expected landfall: {$cyclone['expected_landfall']}. Track: {$cyclone['track']}.",
                    'predicted_at' => now(),
                ]);
            }
        }
    }

    /**
     * Create landslide predictions from rainfall + terrain data
     */
    private function createLandslidePredictions()
    {
        $rainfall = $this->pagasaService->getRainfallData();
        $totalRainfall = array_sum(array_column($rainfall, 'rain'));

        if ($totalRainfall > 60) {
            $exists = DisasterPrediction::where('disaster_type', 'landslide')
                ->where('predicted_at', '>=', now()->subHours(12))
                ->exists();

            if (!$exists) {
                DisasterPrediction::create([
                    'disaster_type' => 'landslide',
                    'latitude' => 14.6760,
                    'longitude' => 121.0437,
                    'location_name' => 'Mountain Areas - Rizal/Quezon Province',
                    'risk_level' => $totalRainfall > 100 ? 9 : 7,
                    'predicted_recovery_days' => 14,
                    'prediction_factors' => "PAGASA Data: Heavy rainfall ({$totalRainfall}mm) detected. Steep terrain. Saturated soil conditions. Historical landslide-prone areas. PhiVolcs geological assessment indicates high susceptibility.",
                    'predicted_at' => now(),
                ]);
            }
        }
    }

    /**
     * Create volcano eruption predictions from PhiVolcs monitoring
     */
    private function createVolcanoPredictions()
    {
        $volcanoes = $this->phivolcsService->getVolcanoStatus();
        
        foreach ($volcanoes as $volcano) {
            if ($volcano['alert_level'] >= 2) {
                $exists = DisasterPrediction::where('disaster_type', 'earthquake')
                    ->where('location_name', 'like', "%{$volcano['name']}%")
                    ->where('predicted_at', '>=', now()->subDays(7))
                    ->exists();

                if (!$exists) {
                    $riskLevel = $volcano['alert_level'] >= 3 ? 9 : 7;
                    
                    DisasterPrediction::create([
                        'disaster_type' => 'earthquake',
                        'latitude' => $volcano['latitude'],
                        'longitude' => $volcano['longitude'],
                        'location_name' => $volcano['name'] . ' Volcanic Activity Zone',
                        'risk_level' => $riskLevel,
                        'predicted_recovery_days' => $riskLevel >= 9 ? 30 : 14,
                        'prediction_factors' => "PhiVolcs Volcano Monitoring: {$volcano['name']} at Alert Level {$volcano['alert_level']}. Status: {$volcano['status']}. {$volcano['description']} Last eruption: {$volcano['last_eruption']}. Increased seismic activity and volcanic tremors detected.",
                        'predicted_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Calculate recovery days based on disaster type and risk level
     */
    private function calculateRecoveryDays($disasterType, $riskLevel)
    {
        $baseDays = [
            'earthquake' => 14,
            'flood' => 5,
            'typhoon' => 10,
            'landslide' => 14,
        ];

        $base = $baseDays[$disasterType] ?? 10;
        
        if ($riskLevel >= 9) return $base * 2;
        if ($riskLevel >= 7) return $base * 1.5;
        return $base;
    }

    /**
     * Get PAGASA weather data
     */
    public function getPagasaData()
    {
        $weather = $this->pagasaService->getCurrentWeather();
        $cyclones = $this->pagasaService->getTropicalCyclones();
        $rainfall = $this->pagasaService->getRainfallData();
        $advisory = $this->pagasaService->getWeatherAdvisory();

        return response()->json([
            'weather' => $weather,
            'tropical_cyclones' => $cyclones,
            'rainfall' => $rainfall,
            'advisory' => $advisory,
            'source' => 'PAGASA',
            'updated_at' => now(),
        ]);
    }

    /**
     * Get PhiVolcs data
     */
    public function getPhivolcsData()
    {
        $earthquakes = $this->phivolcsService->getRecentEarthquakes(20);
        $volcanoes = $this->phivolcsService->getVolcanoStatus();
        $tsunami = $this->phivolcsService->getTsunamiAdvisory();
        $preparedness = $this->phivolcsService->getPreparednessInfo();

        return response()->json([
            'earthquakes' => $earthquakes,
            'volcanoes' => $volcanoes,
            'tsunami_advisory' => $tsunami,
            'preparedness' => $preparedness,
            'source' => 'PhiVolcs',
            'updated_at' => now(),
        ]);
    }

    /**
     * Get NDRRMC data
     */
    public function getNdrmcData()
    {
        $situationReport = $this->ndrmcService->getSituationReport();
        $hotlines = $this->ndrmcService->getEmergencyHotlines();
        $standards = $this->ndrmcService->getEvacuationCenterStandards();

        return response()->json([
            'situation_report' => $situationReport,
            'emergency_hotlines' => $hotlines,
            'evacuation_standards' => $standards,
            'source' => 'NDRRMC',
            'updated_at' => now(),
        ]);
    }

    /**
     * Get preparedness guidelines
     */
    public function getPreparednessGuidelines($disasterType)
    {
        $guidelines = $this->ndrmcService->getPreparednessGuidelines($disasterType);
        
        return response()->json([
            'disaster_type' => $disasterType,
            'guidelines' => $guidelines,
            'source' => 'NDRRMC',
        ]);
    }

    /**
     * Get emergency hotlines
     */
    public function getEmergencyHotlines()
    {
        $hotlines = $this->ndrmcService->getEmergencyHotlines();
        
        return view('real-time-data.hotlines', compact('hotlines'));
    }
}
