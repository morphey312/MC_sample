<?php

namespace App\V1\Providers\Call;

use Illuminate\Support\ServiceProvider;
use App\V1\Observers\RecordChangeObserver;
use App\V1\Models\Call\DeleteReason;

class DeleteReasonServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/call/delete-reason.php'));
        
        DeleteReason::observe(RecordChangeObserver::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Call\DeleteReasonRepository',
            'App\V1\Repositories\Call\DeleteReasonRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Call\DeleteReasonFilter',
            'App\V1\Repositories\Query\Call\DeleteReasonFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Call\DeleteReasonSorter',
            'App\V1\Repositories\Query\Call\DeleteReasonSorter'
        );
    }
}