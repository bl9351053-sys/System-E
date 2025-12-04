<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
// Listeners removed for login tracking - System-E uses shared DB for login histories.

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
    ];
}
