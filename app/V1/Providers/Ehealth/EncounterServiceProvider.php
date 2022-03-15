<?php

namespace App\V1\Providers\Ehealth;

use Illuminate\Support\ServiceProvider;

class EncounterServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/ehealth/encounter.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Ehealth\EncounterRepository',
            'App\V1\Repositories\Ehealth\EncounterRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Ehealth\EncounterFilter',
            'App\V1\Repositories\Query\Ehealth\EncounterFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Ehealth\EncounterSorter',
            'App\V1\Repositories\Query\Ehealth\EncounterSorter'
        );
    }
}
