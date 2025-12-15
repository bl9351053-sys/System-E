<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EvacuationAreaController;
use App\Http\Controllers\DisasterUpdateController;
use App\Http\Controllers\DisasterPredictionController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\RealTimeDataController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmergencyAlertController;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('families', [FamilyController::class, 'index']);
Route::post('families/{family}/checkout', [FamilyController::class, 'checkout']);


Route::get('evacuation-areas', [EvacuationAreaController::class, 'index']);
Route::get('evacuation-areas/nearest', [EvacuationAreaController::class, 'nearest']);
Route::get('evacuation-areas/recommend', [EvacuationAreaController::class, 'recommend']);
Route::post('evacuation-areas/{id}/go', [EvacuationAreaController::class, 'go']);
Route::get('evacuation-areas/{evacuationArea}', [EvacuationAreaController::class, 'show']);
Route::post('evacuation-areas', [EvacuationAreaController::class, 'store']);
Route::put('evacuation-areas/{evacuationArea}', [EvacuationAreaController::class, 'update']);
Route::delete('evacuation-areas/{evacuationArea}', [EvacuationAreaController::class, 'destroy']);


Route::get('disaster-updates', [DisasterUpdateController::class, 'index']);
Route::get('disaster-updates/latest', [DisasterUpdateController::class, 'latest']);
Route::get('disaster-updates/{disasterUpdate}', [DisasterUpdateController::class, 'show']);



Route::get('disaster-predictions', [DisasterPredictionController::class, 'index']);
Route::get('disaster-predictions/active', [DisasterPredictionController::class, 'active']);
Route::get('disaster-predictions/{disasterPrediction}', [DisasterPredictionController::class, 'show']);
Route::delete('disaster-predictions/{disasterPrediction}', [DisasterPredictionController::class, 'destroy']);

Route::get('pagasa/data', [RealTimeDataController::class, 'getPagasaData']);
Route::get('phivolcs/data', [RealTimeDataController::class, 'getPhivolcsData']);
Route::get('ndrrmc/data', [RealTimeDataController::class, 'getNdrmcData']);
Route::get('preparedness/{disasterType}', [RealTimeDataController::class, 'getPreparednessGuidelines']);
Route::get('emergency-hotlines', [RealTimeDataController::class, 'getEmergencyHotlines']);
Route::post('real-time-data/sync', [RealTimeDataController::class, 'syncData']);
Route::get('real-time-data', [RealTimeDataController::class, 'index']);

Route::get('dashboard', [DashboardController::class, 'api']);
Route::get('prescriptive/recommend', [\App\Http\Controllers\PrescriptiveController::class, 'recommend']);

Route::get('/emergency-alerts', [EmergencyAlertController::class, 'index']);
Route::post('/emergency-alerts', [EmergencyAlertController::class, 'store']);
Route::get('/emergency-alerts/{emergencyAlert}', [EmergencyAlertController::class, 'show']);
Route::put('/emergency-alerts/{emergencyAlert}', [EmergencyAlertController::class, 'update']);
Route::delete('/emergency-alerts/{emergencyAlert}', [EmergencyAlertController::class, 'destroy']);


