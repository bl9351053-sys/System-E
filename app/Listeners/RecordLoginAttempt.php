<?php

// This listener file remains as a no-op in System - E because login tracking is centralized
// into System-E_Admin (shared database `userevac_db`). The event listener is intentionally
// disabled in `app/Providers/EventServiceProvider.php` for this repo. 

namespace App\Listeners;

class RecordLoginAttempt
{
    public function handle($event): void
    {
        // no-op
    }
}
