<?php

namespace App\V1\Providers\Employee;

use Illuminate\Support\ServiceProvider;
use App\V1\Models\Employee\DoctorIncomePlan;
use App\V1\Observers\Audit\Employee\DoctorIncomePlanAudit;

class DoctorIncomePlanServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/employee/doctor-income-plan.php'));

        DoctorIncomePlan::observe(DoctorIncomePlanAudit::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Employee\DoctorIncomePlanRepository',
            'App\V1\Repositories\Employee\DoctorIncomePlanRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Employee\DoctorIncomePlanFilter',
            'App\V1\Repositories\Query\Employee\DoctorIncomePlanFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Employee\DoctorIncomePlanSorter',
            'App\V1\Repositories\Query\Employee\DoctorIncomePlanSorter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Employee\DoctorIncomePlanScopes',
            'App\V1\Repositories\Query\Employee\DoctorIncomePlanScopes'
        );
    }
}