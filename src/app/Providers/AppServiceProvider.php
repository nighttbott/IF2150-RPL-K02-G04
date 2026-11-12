<?php

namespace App\Providers;

use App\Models\KelasBelajar;
use App\Policies\KelasBelajarPolicy;
use Illuminate\Support\Facades\Gate;
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
        Gate::policy(KelasBelajar::class, KelasBelajarPolicy::class);
    }
}
