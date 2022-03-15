<?php

namespace App\V1\Providers\Reports;

use Illuminate\Support\ServiceProvider;

class FinanceServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/report/finance-report.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Reports\Finance\ServicesReportRepository',
            'App\V1\Repositories\Reports\Finance\ServicesReportRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Reports\Finance\ServicesReportFilter',
            'App\V1\Repositories\Query\Reports\Finance\ServicesReportFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Reports\Finance\ScheduleReportRepository',
            'App\V1\Repositories\Reports\Finance\ScheduleReportRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Reports\Finance\ScheduleReportFilter',
            'App\V1\Repositories\Query\Reports\Finance\ScheduleReportFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Reports\Finance\IncomeReportRepository',
            'App\V1\Repositories\Reports\Finance\IncomeReportRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Reports\Finance\IncomeReportFilter',
            'App\V1\Repositories\Query\Reports\Finance\IncomeReportFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Reports\Finance\RedirectsReportRepository',
            'App\V1\Repositories\Reports\Finance\RedirectsReportRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Reports\Finance\RedirectsReportFilter',
            'App\V1\Repositories\Query\Reports\Finance\RedirectsReportFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Reports\Finance\DebitorsReportRepository',
            'App\V1\Repositories\Reports\Finance\DebitorsReportRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Reports\Finance\DebitorsReportFilter',
            'App\V1\Repositories\Query\Reports\Finance\DebitorsReportFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Reports\Finance\OnlineServiceReportRepository',
            'App\V1\Repositories\Reports\Finance\OnlineServiceReportRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Reports\Finance\OnlineServiceReportFilter',
            'App\V1\Repositories\Query\Reports\Finance\OnlineServiceReportFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Reports\Finance\DoctorSpecializationReportRepository',
            'App\V1\Repositories\Reports\Finance\DoctorSpecializationReportRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Reports\Finance\DoctorSpecializationReportFilter',
            'App\V1\Repositories\Query\Reports\Finance\DoctorSpecializationReportFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Reports\Finance\InsurancePatientReportRepository',
            'App\V1\Repositories\Reports\Finance\InsurancePatientReportRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Reports\Finance\InsurancePatientReportFilter',
            'App\V1\Repositories\Query\Reports\Finance\InsurancePatientReportFilter'
        );
        
    }
}
