<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;

use App\V1\Observers\RecordChangeObserver;
use App\V1\Models\ReasonUnblock;

class ReasonUnblockServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/reason-unblock.php'));

        ReasonUnblock::observe(RecordChangeObserver::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\ReasonUnblockRepository',
            'App\V1\Repositories\ReasonUnblockRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\ReasonUnblockFilter',
            'App\V1\Repositories\Query\ReasonUnblockFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\ReasonUnblockSorter',
            'App\V1\Repositories\Query\ReasonUnblockSorter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\ReasonUnblockScopes',
            'App\V1\Repositories\Query\ReasonUnblockScopes'
        );
    }
}
