<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;

class MedicineFirmServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/medicine-firm.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\MedicineFirmRepository',
            'App\V1\Repositories\MedicineFirmRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\MedicineFirmFilter',
            'App\V1\Repositories\Query\MedicineFirmFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\MedicineFirmSorter',
            'App\V1\Repositories\Query\MedicineFirmSorter'
        );
    }
}
