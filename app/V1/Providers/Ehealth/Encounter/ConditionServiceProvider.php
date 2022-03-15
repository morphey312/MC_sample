<?php

namespace App\V1\Providers\Ehealth\Encounter;

use Illuminate\Support\ServiceProvider;

class ConditionServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/ehealth/encounter/condition.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Ehealth\Encounter\ConditionRepository',
            'App\V1\Repositories\Ehealth\Encounter\ConditionRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Ehealth\Encounter\ConditionFilter',
            'App\V1\Repositories\Query\Ehealth\Encounter\ConditionFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Ehealth\Encounter\ConditionSorter',
            'App\V1\Repositories\Query\Ehealth\Encounter\ConditionSorter'
        );
    }
}
