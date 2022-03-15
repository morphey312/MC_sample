<?php

namespace App\V1\Providers\Appointment;

use Illuminate\Support\ServiceProvider;
use App\V1\Observers\RecordChangeObserver;
use App\V1\Models\Appointment\Status;

class StatusServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/appointment/status.php'));
        
        Status::observe(RecordChangeObserver::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Appointment\StatusRepository',
            'App\V1\Repositories\Appointment\StatusRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Appointment\StatusFilter',
            'App\V1\Repositories\Query\Appointment\StatusFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Appointment\StatusSorter',
            'App\V1\Repositories\Query\Appointment\StatusSorter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Appointment\StatusScopes',
            'App\V1\Repositories\Query\Appointment\StatusScopes'
        );
    }
}