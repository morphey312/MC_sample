<?php

namespace App\V1\Providers\Appointment;

use Illuminate\Support\ServiceProvider;
use App\V1\Observers\RecordChangeObserver;
use App\V1\Models\Appointment\Limitation;

class LimitationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/appointment/limitation.php'));
        
        Limitation::observe(RecordChangeObserver::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Appointment\LimitationRepository',
            'App\V1\Repositories\Appointment\LimitationRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Appointment\LimitationFilter',
            'App\V1\Repositories\Query\Appointment\LimitationFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Appointment\LimitationSorter',
            'App\V1\Repositories\Query\Appointment\LimitationSorter'
        );
    }
}