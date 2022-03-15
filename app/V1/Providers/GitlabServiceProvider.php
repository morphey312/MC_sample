<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;

class GitlabServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/gitlab.php'));
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
