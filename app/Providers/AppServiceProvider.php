<?php

namespace App\Providers;

use App\Services\FeeService;
use App\Services\PerformanceService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('Helpers/CampusContext.php');
        require_once app_path('Helpers/AcademicYearContext.php');

        $this->app->singleton(FeeService::class);
        $this->app->singleton(PerformanceService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
