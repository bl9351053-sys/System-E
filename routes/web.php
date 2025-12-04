<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EvacuationAreaController;
use App\Http\Controllers\DisasterUpdateController;
use App\Http\Controllers\DisasterPredictionController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\RealTimeDataController;
use App\Http\Controllers\ChooseRoleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Show choose page for guests, dashboard for logged-in users
Route::get('/', [\App\Http\Controllers\ChooseRoleController::class, 'home'])->name('home');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Role chooser landing page — lets a visitor choose Resident or Admin
Route::get('/choose', [ChooseRoleController::class, 'index'])->name('choose-role');
Route::get('/choose/{role}', [ChooseRoleController::class, 'redirect'])->name('choose.redirect');

// Backwards compatible admin redirect.
// If your admin instance is hosted at a different URL/port, set ADMIN_APP_URL in .env
Route::get('/admin', function () {
	$adminUrl = env('ADMIN_APP_URL');
	if ($adminUrl) {
		return redirect($adminUrl);
	}
	// Admin app not configured; show a helpful message and link back to the choose page
	return view('admin-not-configured');
})->name('admin.redirect');

// Logout route for the public site: logs the user out and redirects back to the choose role page
Route::post('/logout', function (Request $request) {
	try {
		Auth::logout();
	} catch (\Throwable $e) {
		// no-op
	}
	$request->session()->invalidate();
	$request->session()->regenerateToken();
	return redirect()->route('home');
})->name('logout');


Route::resource('evacuation-areas', EvacuationAreaController::class);
Route::get('/evacuation-areas', [EvacuationAreaController::class, 'index'])->name('evacuation-areas.index');
Route::post('evacuation-areas/{evacuationArea}/go', [EvacuationAreaController::class, 'go'])->name('evacuation-areas.go');



Route::get('api/evacuation-areas/nearest', [EvacuationAreaController::class, 'nearest'])->name('api.evacuation-areas.nearest');
Route::get('api/evacuation-areas/recommend', [EvacuationAreaController::class, 'recommend'])->name('api.evacuation-areas.recommend');

Route::resource('disaster-updates', DisasterUpdateController::class);
Route::get('api/disaster-updates/latest', [DisasterUpdateController::class, 'latest'])->name('api.disaster-updates.latest');


Route::resource('disaster-predictions', DisasterPredictionController::class);
Route::get('api/disaster-predictions/active', [DisasterPredictionController::class, 'active'])->name('api.disaster-predictions.active');
Route::post('api/disaster-predictions/analyze', [DisasterPredictionController::class, 'analyze'])->name('api.disaster-predictions.analyze');


Route::get('families', [FamilyController::class, 'index'])->name('families.index');
Route::post('families/{family}/checkout', [FamilyController::class, 'checkout'])->name('families.checkout');



Route::get('real-time-data', [RealTimeDataController::class, 'index'])->name('real-time-data.index');
Route::post('real-time-data/sync', [RealTimeDataController::class, 'syncData'])->name('real-time-data.sync');
Route::get('api/pagasa/data', [RealTimeDataController::class, 'getPagasaData'])->name('api.pagasa.data');
Route::get('api/phivolcs/data', [RealTimeDataController::class, 'getPhivolcsData'])->name('api.phivolcs.data');
Route::get('api/ndrrmc/data', [RealTimeDataController::class, 'getNdrmcData'])->name('api.ndrrmc.data');
Route::get('api/preparedness/{disasterType}', [RealTimeDataController::class, 'getPreparednessGuidelines'])->name('api.preparedness');
Route::get('emergency-hotlines', [RealTimeDataController::class, 'getEmergencyHotlines'])->name('emergency-hotlines');




