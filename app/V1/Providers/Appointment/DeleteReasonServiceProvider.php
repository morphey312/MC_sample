<?php

namespace App\V1\Providers\Appointment;

use Illuminate\Support\ServiceProvider;
use App\V1\Observers\RecordChangeObserver;
use App\V1\Models\Appointment\DeleteReason;

class DeleteReasonServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/appointment/delete-reason.php'));
        
        DeleteReason::observe(RecordChangeObserver::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Appointment\DeleteReasonRepository',
            'App\V1\Repositories\Appointment\DeleteReasonRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Appointment\DeleteReasonFilter',
            'App\V1\Repositories\Query\Appointment\DeleteReasonFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Appointment\DeleteReasonSorter',
            'App\V1\Repositories\Query\Appointment\DeleteReasonSorter'
        );
    }
}