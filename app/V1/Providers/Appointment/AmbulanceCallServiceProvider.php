<?php

namespace App\V1\Providers\Appointment;

use App\V1\Models\AmbulanceCall;
use App\V1\Observers\Audit\Appointment\AmbulanceCallAudit;
use Illuminate\Support\ServiceProvider;

class AmbulanceCallServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/appointment/ambulance-call.php'));
        AmbulanceCall::observe(AmbulanceCallAudit::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Appointment\AmbulanceCallRepository',
            'App\V1\Repositories\Appointment\AmbulanceCallRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Appointment\AmbulanceCallFilter',
            'App\V1\Repositories\Query\Appointment\AmbulanceCallFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Appointment\AmbulanceCallSorter',
            'App\V1\Repositories\Query\Appointment\AmbulanceCallSorter'
        );
    }
}
