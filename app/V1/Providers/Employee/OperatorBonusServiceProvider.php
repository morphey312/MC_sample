<?php

namespace App\V1\Providers\Employee;

use Illuminate\Support\ServiceProvider;
use App\V1\Observers\RecordChangeObserver;
use App\V1\Models\Employee\OperatorBonus;

class OperatorBonusServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        OperatorBonus::observe(RecordChangeObserver::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Employee\OperatorBonusRepository',
            'App\V1\Repositories\Employee\OperatorBonusRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Employee\OperatorBonusFilter',
            'App\V1\Repositories\Query\Employee\OperatorBonusFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Employee\OperatorBonusSorter',
            'App\V1\Repositories\Query\Employee\OperatorBonusSorter'
        );
    }
}