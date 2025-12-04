<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\EvacuationArea;
use App\Models\Family;

class NdrmcApiService
{
    /**
     * NDRRMC (National Disaster Risk Reduction and Management Council) Service
     */
    private const NDRRMC_BASE_URL = 'https://ndrrmc.gov.ph';
    
    /**
     * Get current disaster situation report
     */
    public function getSituationReport()
    {
        return Cache::remember('ndrrmc_sitrep', 1800, function () {
            try {
                // In production, fetch actual NDRRMC situation reports
                // This provides structured data based on NDRRMC reporting format
                
                $evacuationStats = $this->getEvacuationStatistics();
                $affectedAreas = $this->getAffectedAreas();
                
                return [
                    'report_number' => 'SITREP-' . now()->format('Ymd-His'),
                    'date_time' => now()->format('Y-m-d H:i:s'),
                    'reporting_period' => now()->format('F d, Y'),
                    'evacuation_centers' => [
                        'total' => $evacuationStats['total_centers'],
                        'occupied' => $evacuationStats['occupied_centers'],
                        'families' => $evacuationStats['total_families'],
                        'persons' => $evacuationStats['total_persons'],
                    ],
                    'affected_areas' => $affectedAreas,
                    'casualties' => $this->getCasualtyReport(),
                    'damages' => $this->getDamageAssessment(),
                    'response_actions' => $this->getResponseActions(),
                    'source' => 'NDRRMC',
                    'updated_at' => now(),
                ];
            } catch (\Exception $e) {
                Log::error('NDRRMC API Error: ' . $e->getMessage());
            }

            return null;
        });
    }

    /**
     * Get evacuation statistics from database
     */
    private function getEvacuationStatistics()
    {
        $totalCenters = EvacuationArea::count();
        $occupiedCenters = EvacuationArea::where('current_occupancy', '>', 0)->count();
        $totalFamilies = Family::whereNull('checked_out_at')->count();
        $totalPersons = Family::whereNull('checked_out_at')->sum('number_of_members');

        return [
            'total_centers' => $totalCenters,
            'occupied_centers' => $occupiedCenters,
            'total_families' => $totalFamilies,
            'total_persons' => $totalPersons,
        ];
    }

    /**
     * Get affected areas
     */
    private function getAffectedAreas()
    {
        // In production, this would come from NDRRMC reports
        return [
            [
                'region' => 'NCR (National Capital Region)',
                'provinces' => ['Metro Manila'],
                'municipalities' => 17,
                'barangays' => 150,
                'families_affected' => Family::whereNull('checked_out_at')->count(),
                'persons_affected' => Family::whereNull('checked_out_at')->sum('number_of_members'),
            ],
        ];
    }

    /**
     * Get casualty report
     */
    private function getCasualtyReport()
    {
        // In production, this would come from NDRRMC official reports
        return [
            'dead' => 0,
            'injured' => 0,
            'missing' => 0,
        ];
    }

    /**
     * Get damage assessment
     */
    private function getDamageAssessment()
    {
        // In production, this would come from NDRRMC damage reports
        return [
            'infrastructure' => [
                'roads' => 0,
                'bridges' => 0,
                'schools' => 0,
                'health_facilities' => 0,
            ],
            'agriculture' => [
                'affected_hectares' => 0,
                'estimated_cost' => 0,
            ],
            'estimated_total_cost' => 0,
        ];
    }

    /**
     * Get response actions
     */
    private function getResponseActions()
    {
        return [
            'preemptive_evacuation' => 'Ongoing in identified high-risk areas',
            'relief_operations' => 'Distribution of food packs and essential supplies',
            'search_and_rescue' => 'Teams on standby',
            'medical_assistance' => 'Mobile health units deployed',
            'coordination' => 'Regular coordination with LGUs and response clusters',
        ];
    }

    /**
     * Get emergency hotlines
     */
    public function getEmergencyHotlines()
    {
        return [
            'ndrrmc' => [
                'name' => 'NDRRMC Operations Center',
                'numbers' => ['(02) 8911-1406', '(02) 8911-5061 to 65'],
                'email' => 'ops.center@ndrrmc.gov.ph',
            ],
            'pagasa' => [
                'name' => 'PAGASA Weather Division',
                'numbers' => ['(02) 8284-0800', '(02) 8927-1335'],
                'email' => 'inquiry@pagasa.dost.gov.ph',
            ],
            'phivolcs' => [
                'name' => 'PhiVolcs Earthquake Monitoring',
                'numbers' => ['(02) 8426-1468 to 79'],
                'email' => 'director@phivolcs.dost.gov.ph',
            ],
            'pnp' => [
                'name' => 'Philippine National Police',
                'numbers' => ['911', '(02) 8722-0650'],
            ],
            'bfp' => [
                'name' => 'Bureau of Fire Protection',
                'numbers' => ['(02) 8426-0219', '(02) 8426-3812'],
            ],
            'red_cross' => [
                'name' => 'Philippine Red Cross',
                'numbers' => ['143', '(02) 8790-2300'],
                'email' => 'info@redcross.org.ph',
            ],
            'coast_guard' => [
                'name' => 'Philippine Coast Guard',
                'numbers' => ['(02) 8527-8481 to 89'],
            ],
            'mmda' => [
                'name' => 'MMDA Metrobase',
                'numbers' => ['(02) 8882-4150', '136'],
            ],
        ];
    }

    /**
     * Get disaster preparedness guidelines
     */
    public function getPreparednessGuidelines($disasterType)
    {
        $guidelines = [
            'typhoon' => [
                'before' => [
                    'Monitor PAGASA weather bulletins regularly',
                    'Prepare emergency kit (flashlight, radio, first aid, food, water)',
                    'Secure loose objects outside your home',
                    'Charge mobile phones and power banks',
                    'Know your evacuation center location',
                    'Prepare important documents in waterproof container',
                ],
                'during' => [
                    'Stay indoors and away from windows',
                    'Monitor official weather updates',
                    'Do not wade through floodwaters',
                    'Turn off main power switch if flooding occurs',
                    'Evacuate immediately if advised by authorities',
                ],
                'after' => [
                    'Check for injuries and damages',
                    'Avoid floodwaters (may be contaminated)',
                    'Report damaged power lines',
                    'Document damages for insurance claims',
                    'Boil water before drinking',
                ],
            ],
            'earthquake' => [
                'before' => [
                    'Secure heavy furniture and appliances',
                    'Identify safe spots (under sturdy tables, door frames)',
                    'Prepare emergency kit',
                    'Know your building\'s evacuation routes',
                    'Practice DROP, COVER, HOLD ON drills',
                ],
                'during' => [
                    'DROP, COVER, and HOLD ON',
                    'Stay away from windows and heavy objects',
                    'If outdoors, move to open area away from buildings',
                    'If in vehicle, pull over and stop',
                    'Do not use elevators',
                ],
                'after' => [
                    'Check for injuries and provide first aid',
                    'Inspect home for damage',
                    'Be prepared for aftershocks',
                    'Stay away from damaged buildings',
                    'Listen to radio for updates',
                ],
            ],
            'flood' => [
                'before' => [
                    'Monitor weather forecasts and flood warnings',
                    'Prepare evacuation kit',
                    'Move valuables to higher ground',
                    'Know evacuation routes',
                    'Prepare emergency contacts list',
                ],
                'during' => [
                    'Move to higher ground immediately',
                    'Do not walk or drive through floodwaters',
                    'Turn off electricity and gas',
                    'Bring pets and livestock to safety',
                    'Follow evacuation orders',
                ],
                'after' => [
                    'Return home only when authorities say it\'s safe',
                    'Avoid floodwaters (contamination risk)',
                    'Clean and disinfect everything touched by floodwater',
                    'Check for structural damage',
                    'Throw away contaminated food',
                ],
            ],
            'landslide' => [
                'before' => [
                    'Know if you live in landslide-prone area',
                    'Monitor rainfall intensity',
                    'Prepare evacuation plan',
                    'Watch for warning signs (cracks, tilting)',
                    'Plant ground cover on slopes',
                ],
                'during' => [
                    'Move away from the path of landslide',
                    'Move to higher ground if possible',
                    'Alert neighbors',
                    'Listen for unusual sounds (trees cracking, boulders)',
                ],
                'after' => [
                    'Stay away from slide area',
                    'Watch for additional slides',
                    'Check for injured and trapped persons',
                    'Report damaged utilities',
                    'Replant damaged ground cover',
                ],
            ],
        ];

        return $guidelines[$disasterType] ?? [];
    }

    /**
     * Get evacuation center standards (NDRRMC guidelines)
     */
    public function getEvacuationCenterStandards()
    {
        return [
            'space_requirements' => [
                'minimum_per_person' => '3.5 square meters',
                'recommended_per_family' => '16 square meters',
            ],
            'basic_facilities' => [
                'Separate toilets for male and female',
                'Washing and bathing facilities',
                'Cooking area',
                'Sleeping area',
                'Storage for relief goods',
                'First aid station',
            ],
            'minimum_services' => [
                'Potable water supply',
                'Adequate lighting',
                'Proper ventilation',
                'Waste disposal system',
                'Communication facilities',
                'Security personnel',
            ],
            'health_standards' => [
                'One toilet per 20 persons',
                'One water point per 80-100 persons',
                'Regular health monitoring',
                'Sanitation and hygiene promotion',
            ],
        ];
    }

    /**
     * Get real-time alerts
     */
    public function getRealTimeAlerts()
    {
        return Cache::remember('ndrrmc_alerts', 300, function () {
            // Aggregate alerts from PAGASA and PhiVolcs
            $alerts = [];

            // Check for weather alerts
            $pagasaService = new PagasaApiService();
            $floodWarnings = $pagasaService->getFloodWarnings();
            
            foreach ($floodWarnings as $warning) {
                $alerts[] = [
                    'type' => 'flood',
                    'severity' => $warning['severity'],
                    'message' => $warning['message'],
                    'location' => $warning['location'],
                    'source' => 'PAGASA',
                    'timestamp' => $warning['timestamp'],
                ];
            }

            // Check for earthquake alerts
            $phivolcsService = new PhivolcsApiService();
            $earthquakes = $phivolcsService->getRecentEarthquakes(3);
            
            foreach ($earthquakes as $quake) {
                if ($quake['magnitude'] >= 4.0) {
                    $alerts[] = [
                        'type' => 'earthquake',
                        'severity' => $quake['significance'],
                        'message' => "Magnitude {$quake['magnitude']} earthquake detected",
                        'location' => $quake['location'],
                        'source' => 'PhiVolcs',
                        'timestamp' => strtotime($quake['timestamp']),
                    ];
                }
            }

            return $alerts;
        });
    }
}
