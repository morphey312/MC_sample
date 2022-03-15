<?php

namespace App\V1\Providers\Employee\Cashbox;

use Illuminate\Support\ServiceProvider;

class CashTransferServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/employee/cashbox/cash-transfer.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Employee\Cashbox\CashTransferRepository',
            'App\V1\Repositories\Employee\Cashbox\CashTransferRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Employee\Cashbox\CashTransferFilter',
            'App\V1\Repositories\Query\Employee\Cashbox\CashTransferFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Employee\Cashbox\CashTransferSorter',
            'App\V1\Repositories\Query\Employee\Cashbox\CashTransferSorter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Employee\Cashbox\CashTransferScopes',
            'App\V1\Repositories\Query\Employee\Cashbox\CashTransferScopes'
        );
    }
}