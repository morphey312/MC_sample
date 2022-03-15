<?php

namespace App\V1\Providers\Msp;

use Illuminate\Support\ServiceProvider;

class ContractServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/msp/contract.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Msp\ContractRepository',
            'App\V1\Repositories\Msp\ContractRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Msp\ContractFilter',
            'App\V1\Repositories\Query\Msp\ContractFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Msp\ContractSorter',
            'App\V1\Repositories\Query\Msp\ContractSorter'
        );
    }
}
