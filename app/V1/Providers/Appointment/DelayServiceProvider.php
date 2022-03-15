<?php

namespace App\V1\Providers\Appointment;

use Illuminate\Support\ServiceProvider;

class DelayServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Appointment\DelayRepository',
            'App\V1\Repositories\Appointment\DelayRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Appointment\DelayFilter',
            'App\V1\Repositories\Query\Appointment\DelayFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Appointment\DelaySorter',
            'App\V1\Repositories\Query\Appointment\DelaySorter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Appointment\DelayScopes',
            'App\V1\Repositories\Query\Appointment\DelayScopes'
        );
    }
}