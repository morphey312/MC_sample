<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;

class EmailLogServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/email-log.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\EmailLogRepository',
            'App\V1\Repositories\EmailLogRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\EmailLogFilter',
            'App\V1\Repositories\Query\EmailLogFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\EmailLogSorter',
            'App\V1\Repositories\Query\EmailLogSorter'
        );
    }
}
