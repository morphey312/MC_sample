<?php

namespace App\V1\Providers\LegalEntity;

use Illuminate\Support\ServiceProvider;

class ClinicServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/legal-entity/clinic.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\LegalEntity\ClinicRepository',
            'App\V1\Repositories\LegalEntity\ClinicRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\LegalEntity\ClinicFilter',
            'App\V1\Repositories\Query\LegalEntity\ClinicFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\LegalEntity\ClinicSorter',
            'App\V1\Repositories\Query\LegalEntity\ClinicSorter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\LegalEntity\ClinicScopes',
            'App\V1\Repositories\Query\LegalEntity\ClinicScopes'
        );
    }
}