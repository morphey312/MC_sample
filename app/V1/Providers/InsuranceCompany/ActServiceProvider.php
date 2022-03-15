<?php

namespace App\V1\Providers\InsuranceCompany;

use Illuminate\Support\ServiceProvider;
use App\V1\Observers\Audit\InsuranceCompany\ActAudit;
use App\V1\Models\InsuranceCompany\Act;

class ActServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/insurance-company/act.php'));
        
        Act::observe(ActAudit::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\InsuranceCompany\ActRepository',
            'App\V1\Repositories\InsuranceCompany\ActRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\InsuranceCompany\ActFilter',
            'App\V1\Repositories\Query\InsuranceCompany\ActFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\InsuranceCompany\ActSorter',
            'App\V1\Repositories\Query\InsuranceCompany\ActSorter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\InsuranceCompany\ActScopes',
            'App\V1\Repositories\Query\InsuranceCompany\ActScopes'
        );
    }
}