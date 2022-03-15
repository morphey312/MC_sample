<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;

class MedicineStoreServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/medicine-store.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\MedicineStoreRepository',
            'App\V1\Repositories\MedicineStoreRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\MedicineStoreFilter',
            'App\V1\Repositories\Query\MedicineStoreFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\MedicineStoreSorter',
            'App\V1\Repositories\Query\MedicineStoreSorter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\MedicineStoreScopes',
            'App\V1\Repositories\Query\MedicineStoreScopes'
        );
    }
}