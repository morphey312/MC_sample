<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;

class DiagnosisServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/diagnosis.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\DiagnosisRepository',
            'App\V1\Repositories\DiagnosisRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\DiagnosisFilter',
            'App\V1\Repositories\Query\DiagnosisFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\DiagnosisSorter',
            'App\V1\Repositories\Query\DiagnosisSorter'
        );
    }
}
