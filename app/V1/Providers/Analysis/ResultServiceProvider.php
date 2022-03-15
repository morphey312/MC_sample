<?php

namespace App\V1\Providers\Analysis;

use App\V1\Models\Patient\Card\Record;
use App\V1\Observers\Audit\Analysis\ResultCardRecordAppointmentAudit;
use Illuminate\Support\ServiceProvider;
use App\V1\Observers\Analysis\ResultObserver;
use App\V1\Models\Analysis\Result;
use App\V1\Observers\Audit\Analysis\ResultAudit;
use App\V1\Observers\Audit\Analysis\ResultAppointmentAudit;

class ResultServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/analysis/result.php'));

        Result::observe(ResultObserver::class);
        Result::observe(ResultAudit::class);
        Result::observe(ResultAppointmentAudit::class);
        Record::observe(ResultCardRecordAppointmentAudit::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Analysis\ResultRepository',
            'App\V1\Repositories\Analysis\ResultRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Analysis\ResultFilter',
            'App\V1\Repositories\Query\Analysis\ResultFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Analysis\ResultSorter',
            'App\V1\Repositories\Query\Analysis\ResultSorter'
        );
    }
}
