<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;

class WaitListRecordServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/wait-list-record.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\WaitListRecordRepository',
            'App\V1\Repositories\WaitListRecordRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\WaitListRecordFilter',
            'App\V1\Repositories\Query\WaitListRecordFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\WaitListRecordSorter',
            'App\V1\Repositories\Query\WaitListRecordSorter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\WaitListRecordScopes',
            'App\V1\Repositories\Query\WaitListRecordScopes'
        );
    }
}