<?php

namespace App\V1\Providers\Reports;

use Illuminate\Support\ServiceProvider;

class MarketingServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/report/marketing-report.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Reports\Marketing\CitiesReportRepository',
            'App\V1\Repositories\Reports\Marketing\CitiesReportRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Reports\Marketing\CitiesReportFilter',
            'App\V1\Repositories\Query\Reports\Marketing\CitiesReportFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Reports\Marketing\TotalsReportRepository',
            'App\V1\Repositories\Reports\Marketing\TotalsReportRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Reports\Marketing\TotalsReportFilter',
            'App\V1\Repositories\Query\Reports\Marketing\TotalsReportFilter'
        );
    }
}
