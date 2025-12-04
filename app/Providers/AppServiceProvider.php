<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Family;

use App\Models\EvacuationArea;
use App\Models\DisasterPrediction;
use App\Models\DisasterUpdate;
use App\Observers\AuditObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register model observers for audit logging
        Family::observe(AuditObserver::class);
     
        EvacuationArea::observe(AuditObserver::class);
        if (class_exists(DisasterPrediction::class)) {
            DisasterPrediction::observe(AuditObserver::class);
        }
        if (class_exists(DisasterUpdate::class)) {
            DisasterUpdate::observe(AuditObserver::class);
        }
    }
}
