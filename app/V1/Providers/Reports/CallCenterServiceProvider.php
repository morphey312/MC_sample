<?php

namespace App\V1\Providers\Reports;

use Illuminate\Support\ServiceProvider;

class CallCenterServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/report/call-center-report.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Reports\CallCenter\OperatorsReportRepository',
            'App\V1\Repositories\Reports\CallCenter\OperatorsReportRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Reports\CallCenter\OperatorsReportFilter',
            'App\V1\Repositories\Query\Reports\CallCenter\OperatorsReportFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Reports\CallCenter\SubjectsReportRepository',
            'App\V1\Repositories\Reports\CallCenter\SubjectsReportRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Reports\CallCenter\SubjectsReportFilter',
            'App\V1\Repositories\Query\Reports\CallCenter\SubjectsReportFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Reports\CallCenter\RefusedTreatmentRepository',
            'App\V1\Repositories\Reports\CallCenter\RefusedTreatmentRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Reports\CallCenter\RefusedTreatmentFilter',
            'App\V1\Repositories\Query\Reports\CallCenter\RefusedTreatmentFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Reports\CallCenter\CallsIncomeReportRepository',
            'App\V1\Repositories\Reports\CallCenter\CallsIncomeReportRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Reports\CallCenter\CallsIncomeReportFilter',
            'App\V1\Repositories\Query\Reports\CallCenter\CallsIncomeReportFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Reports\CallCenter\SlicesReportRepository',
            'App\V1\Repositories\Reports\CallCenter\SlicesReportRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Reports\CallCenter\SlicesReportFilter',
            'App\V1\Repositories\Query\Reports\CallCenter\SlicesReportFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Reports\CallCenter\OperatorBonusesReportRepository',
            'App\V1\Repositories\Reports\CallCenter\OperatorBonusesReportRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Reports\CallCenter\OperatorBonusesReportFilter',
            'App\V1\Repositories\Query\Reports\CallCenter\OperatorBonusesReportFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Reports\CallCenter\OperatorNormReportRepository',
            'App\V1\Repositories\Reports\CallCenter\OperatorNormReportRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Reports\CallCenter\CollectorsReportRepository',
            'App\V1\Repositories\Reports\CallCenter\CollectorsReportRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Reports\CallCenter\CollectorsReportFilter',
            'App\V1\Repositories\Query\Reports\CallCenter\CollectorsReportFilter'
        );
    }
}