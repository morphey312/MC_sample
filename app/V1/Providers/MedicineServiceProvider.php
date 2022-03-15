<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;

class MedicineServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/medicine.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\MedicineRepository',
            'App\V1\Repositories\MedicineRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\MedicineFilter',
            'App\V1\Repositories\Query\MedicineFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\MedicineSorter',
            'App\V1\Repositories\Query\MedicineSorter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\MedicineScopes',
            'App\V1\Repositories\Query\MedicineScopes'
        );
    }
}