<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use App\V1\Models\Handbook;
use App\V1\Observers\HandbookObserver;
use App\V1\Observers\RecordChangeObserver;

class HandbookServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/handbook.php'));
        
        Handbook::observe(HandbookObserver::class);
        Handbook::observe(RecordChangeObserver::class);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\HandbookRepository',
            'App\V1\Repositories\HandbookRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\HandbookFilter',
            'App\V1\Repositories\Query\HandbookFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\HandbookSorter',
            'App\V1\Repositories\Query\HandbookSorter'
        );

        $this->app->bind(
            'App\V1\Contracts\Services\HandbookService',
            'App\V1\Services\HandbookService'
        );

        $this->app->bind(
            'handbook', 
            'App\V1\Contracts\Services\HandbookService'
        );
    }
}
