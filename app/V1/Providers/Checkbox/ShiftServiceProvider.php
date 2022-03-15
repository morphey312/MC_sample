<?php

namespace App\V1\Providers\Checkbox;

use Illuminate\Support\ServiceProvider;

class ShiftServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/checkbox/shifts.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\\Checkbox\ShiftRepository',
            'App\V1\Repositories\\Checkbox\ShiftRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\\Checkbox\ShiftFilter',
            'App\V1\Repositories\Query\\Checkbox\ShiftFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\\Checkbox\ShiftSorter',
            'App\V1\Repositories\Query\\Checkbox\ShiftSorter'
        );
    }
}