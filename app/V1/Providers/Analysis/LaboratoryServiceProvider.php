<?php

namespace App\V1\Providers\Analysis;

use Illuminate\Support\ServiceProvider;
use App\V1\Observers\RecordChangeObserver;
use App\V1\Models\Analysis\Laboratory;

class LaboratoryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/analysis/laboratory.php'));

        Laboratory::observe(RecordChangeObserver::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Analysis\LaboratoryRepository',
            'App\V1\Repositories\Analysis\LaboratoryRepository'
        );
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Analysis\LaboratoryFilter',
            'App\V1\Repositories\Query\Analysis\LaboratoryFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Analysis\LaboratorySorter',
            'App\V1\Repositories\Query\Analysis\LaboratorySorter'
        );

        $this->app->bind(
            'App\V1\Contracts\Services\LaboratoryClientService',
            'App\V1\Services\LaboratoryClientService'
        );
    }
}
