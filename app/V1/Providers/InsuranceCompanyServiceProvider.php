<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;

class InsuranceCompanyServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/insurance-company.php'));
        $this->loadRoutesFrom(base_path('routes/modules/v1/insurance-company/price.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\InsuranceCompanyRepository',
            'App\V1\Repositories\InsuranceCompanyRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\InsuranceCompanyFilter',
            'App\V1\Repositories\Query\InsuranceCompanyFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\InsuranceCompanySorter',
            'App\V1\Repositories\Query\InsuranceCompanySorter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\InsuranceCompanyScopes',
            'App\V1\Repositories\Query\InsuranceCompanyScopes'
        );
    }
}