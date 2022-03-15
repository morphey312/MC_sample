<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;

class Apteka24ServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/apteka24.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {

    }
}
