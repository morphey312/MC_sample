<?php

namespace App\V1\Providers\Checkbox;

use Illuminate\Support\ServiceProvider;

class CheckServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/checkbox/checks.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Checkbox\CheckRepository',
            'App\V1\Repositories\Checkbox\CheckRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Checkbox\CheckFilter',
            'App\V1\Repositories\Query\Checkbox\CheckFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Checkbox\CheckSorter',
            'App\V1\Repositories\Query\Checkbox\CheckSorter'
        );
    }
}
