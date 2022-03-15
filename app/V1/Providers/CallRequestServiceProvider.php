<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;
use App\V1\Models\CallRequest;
use App\V1\Observers\Audit\CallRequestAudit;
use App\V1\Observers\RecordChangeObserver;

class CallRequestServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/call-request.php'));
        
        CallRequest::observe(RecordChangeObserver::class);
        CallRequest::observe(CallRequestAudit::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\CallRequestRepository',
            'App\V1\Repositories\CallRequestRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\CallRequestFilter',
            'App\V1\Repositories\Query\CallRequestFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\CallRequestSorter',
            'App\V1\Repositories\Query\CallRequestSorter'
        );
    }
}