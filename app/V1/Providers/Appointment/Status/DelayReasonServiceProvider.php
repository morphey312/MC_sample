<?php

namespace App\V1\Providers\Appointment\Status;

use Illuminate\Support\ServiceProvider;

class DelayReasonServiceProvider extends ServiceProvider
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
            'App\V1\Contracts\Repositories\Appointment\Status\DelayReasonRepository',
            'App\V1\Repositories\Appointment\Status\DelayReasonRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Appointment\Status\DelayReasonFilter',
            'App\V1\Repositories\Query\Appointment\Status\DelayReasonFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Appointment\Status\DelayReasonSorter',
            'App\V1\Repositories\Query\Appointment\Status\DelayReasonSorter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Appointment\Status\DelayReasonScopes',
            'App\V1\Repositories\Query\Appointment\Status\DelayReasonScopes'
        );
    }
}