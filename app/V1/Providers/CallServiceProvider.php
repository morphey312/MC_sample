<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;
use App\V1\Models\Call;
use App\V1\Observers\Audit\CallAudit;
use App\V1\Observers\RecordChangeObserver;
use App\V1\Observers\Elastic\CallCenter\CallObserver as ElasticCallObserver;

class CallServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/call.php'));
        
        Call::observe(RecordChangeObserver::class);
        Call::observe(CallAudit::class);
        Call::observe(ElasticCallObserver::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\CallRepository',
            'App\V1\Repositories\CallRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\CallFilter',
            'App\V1\Repositories\Query\CallFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\CallSorter',
            'App\V1\Repositories\Query\CallSorter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\CallScopes',
            'App\V1\Repositories\Query\CallScopes'
        );
    }
}