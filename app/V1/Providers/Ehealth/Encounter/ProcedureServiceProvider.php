<?php

namespace App\V1\Providers\Ehealth\Encounter;

use Illuminate\Support\ServiceProvider;

class ProcedureServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/ehealth/encounter/procedure.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Ehealth\Encounter\ProcedureRepository',
            'App\V1\Repositories\Ehealth\Encounter\ProcedureRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Ehealth\Encounter\ProcedureFilter',
            'App\V1\Repositories\Query\Ehealth\Encounter\ProcedureFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Ehealth\Encounter\ProcedureSorter',
            'App\V1\Repositories\Query\Ehealth\Encounter\ProcedureSorter'
        );
    }
}
