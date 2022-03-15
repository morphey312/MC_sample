<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;
use App\V1\Models\Service;
use App\V1\Observers\Audit\ServiceAudit;
use App\V1\Observers\RecordChangeObserver;

class ServiceServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/service.php'));
        
        Service::observe(RecordChangeObserver::class);
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
            'App\V1\Contracts\Repositories\ServiceRepository',
            'App\V1\Repositories\ServiceRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\ServiceFilter',
            'App\V1\Repositories\Query\ServiceFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\ServiceSorter',
            'App\V1\Repositories\Query\ServiceSorter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\ServiceScopes',
            'App\V1\Repositories\Query\ServiceScopes'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Services\Merge\Service',
            'App\V1\Services\Merge\Service'
        );
    }
}