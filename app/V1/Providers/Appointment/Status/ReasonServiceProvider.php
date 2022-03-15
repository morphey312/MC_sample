<?php

namespace App\V1\Providers\Appointment\Status;

use Illuminate\Support\ServiceProvider;
use App\V1\Observers\RecordChangeObserver;
use App\V1\Models\Appointment\Status\Reason;

class ReasonServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        Reason::observe(RecordChangeObserver::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Appointment\Status\ReasonRepository',
            'App\V1\Repositories\Appointment\Status\ReasonRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Appointment\Status\ReasonFilter',
            'App\V1\Repositories\Query\Appointment\Status\ReasonFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Appointment\Status\ReasonSorter',
            'App\V1\Repositories\Query\Appointment\Status\ReasonSorter'
        );
    }
}