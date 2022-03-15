<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;

class CheckboxServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/checkbox.php'));
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Services\CheckboxService',
            'App\V1\Services\CheckboxService'
        );
    }
}