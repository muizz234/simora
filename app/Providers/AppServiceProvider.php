<?php

namespace App\Providers;

use App\Models\Organization;
use App\Models\User;
use App\Observers\OrganizationObserver;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;

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
        // Event sinkronisasi antar modul (outbox domain_events).
        Organization::observe(OrganizationObserver::class);
        User::observe(UserObserver::class);
    }
}
