<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;

class CashierSessionLogServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/cashier-session-log.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\CashierSessionLogRepository',
            'App\V1\Repositories\CashierSessionLogRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\CashierSessionLogFilter',
            'App\V1\Repositories\Query\CashierSessionLogFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\CashierSessionLogSorter',
            'App\V1\Repositories\Query\CashierSessionLogSorter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\CashierSessionLogScopes',
            'App\V1\Repositories\Query\CashierSessionLogScopes'
        );
    }
}