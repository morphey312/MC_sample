<?php

namespace App\V1\Providers\Employee;

use Illuminate\Support\ServiceProvider;
use App\V1\Models\Employee\Clinic;
use App\V1\Observers\Audit\Employee\ClinicAudit;
use App\V1\Observers\HrPortal\UpdateEmployeeStatusInPortalObserver;

class ClinicServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/employee/clinic.php'));

        Clinic::observe(ClinicAudit::class);
        Clinic::observe(UpdateEmployeeStatusInPortalObserver::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Employee\ClinicRepository',
            'App\V1\Repositories\Employee\ClinicRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Employee\ClinicFilter',
            'App\V1\Repositories\Query\Employee\ClinicFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Employee\ClinicSorter',
            'App\V1\Repositories\Query\Employee\ClinicSorter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Employee\ClinicScopes',
            'App\V1\Repositories\Query\Employee\ClinicScopes'
        );
    }
}
