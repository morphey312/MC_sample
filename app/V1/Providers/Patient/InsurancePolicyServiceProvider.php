<?php

namespace App\V1\Providers\Patient;

use Illuminate\Support\ServiceProvider;
use App\V1\Models\Patient\InsurancePolicy;
use App\V1\Observers\Audit\Patient\InsurancePolicyAudit;

class InsurancePolicyServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/patient/insurance-policy.php'));
        InsurancePolicy::observe(InsurancePolicyAudit::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Patient\InsurancePolicyRepository',
            'App\V1\Repositories\Patient\InsurancePolicyRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\InsurancePolicyFilter',
            'App\V1\Repositories\Query\Patient\InsurancePolicyFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\InsurancePolicySorter',
            'App\V1\Repositories\Query\Patient\InsurancePolicySorter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\InsurancePolicyScopes',
            'App\V1\Repositories\Query\Patient\InsurancePolicyScopes'
        );
    }
}