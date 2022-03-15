<?php

namespace App\V1\Providers\CallRequest;

use Illuminate\Support\ServiceProvider;
use App\V1\Observers\RecordChangeObserver;
use App\V1\Models\CallRequest\Purpose;

class PurposeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/call_request/purpose.php'));
        
        Purpose::observe(RecordChangeObserver::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\CallRequest\PurposeRepository',
            'App\V1\Repositories\CallRequest\PurposeRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\CallRequest\PurposeFilter',
            'App\V1\Repositories\Query\CallRequest\PurposeFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\CallRequest\PurposeSorter',
            'App\V1\Repositories\Query\CallRequest\PurposeSorter'
        );
    }
}