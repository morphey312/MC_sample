<?php

namespace App\V1\Providers\Ehealth\Encounter;

use Illuminate\Support\ServiceProvider;

class DiagnosticReportServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/ehealth/encounter/diagnostic-report.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Ehealth\Encounter\DiagnosticReportRepository',
            'App\V1\Repositories\Ehealth\Encounter\DiagnosticReportRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Ehealth\Encounter\DiagnosticReportFilter',
            'App\V1\Repositories\Query\Ehealth\Encounter\DiagnosticReportFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Ehealth\Encounter\DiagnosticReportSorter',
            'App\V1\Repositories\Query\Ehealth\Encounter\DiagnosticReportSorter'
        );
    }
}
