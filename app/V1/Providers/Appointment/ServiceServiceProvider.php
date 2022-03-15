<?php

namespace App\V1\Providers\Appointment;

use Illuminate\Support\ServiceProvider;
use App\V1\Models\Appointment\Service;
use App\V1\Observers\Audit\Appointment\ServiceAudit;

class ServiceServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/appointment/service.php'));
        Service::observe(ServiceAudit::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Appointment\ServiceRepository',
            'App\V1\Repositories\Appointment\ServiceRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Appointment\ServiceFilter',
            'App\V1\Repositories\Query\Appointment\ServiceFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Appointment\ServiceSorter',
            'App\V1\Repositories\Query\Appointment\ServiceSorter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Appointment\ServiceScopes',
            'App\V1\Repositories\Query\Appointment\ServiceScopes'
        );
    }
}