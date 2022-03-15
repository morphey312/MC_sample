<?php

namespace App\V1\Providers\Call;

use Illuminate\Support\ServiceProvider;
use App\V1\Observers\RecordChangeObserver;
use App\V1\Models\Call\Result;

class ResultServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/call/result.php'));

        Result::observe(RecordChangeObserver::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Call\ResultRepository',
            'App\V1\Repositories\Call\ResultRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Call\ResultFilter',
            'App\V1\Repositories\Query\Call\ResultFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Call\ResultSorter',
            'App\V1\Repositories\Query\Call\ResultSorter'
        );
    }
}
