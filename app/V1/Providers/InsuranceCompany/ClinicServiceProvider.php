<?php

namespace App\V1\Providers\InsuranceCompany;

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
        $this->loadRoutesFrom(base_path('routes/modules/v1/insurance-company/clinic.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\InsuranceCompany\ClinicRepository',
            'App\V1\Repositories\InsuranceCompany\ClinicRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\InsuranceCompany\ClinicFilter',
            'App\V1\Repositories\Query\InsuranceCompany\ClinicFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\InsuranceCompany\ClinicSorter',
            'App\V1\Repositories\Query\InsuranceCompany\ClinicSorter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\InsuranceCompany\ClinicScopes',
            'App\V1\Repositories\Query\InsuranceCompany\ClinicScopes'
        );
    }
}