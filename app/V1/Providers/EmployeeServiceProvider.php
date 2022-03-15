<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;
use App\V1\Observers\RecordChangeObserver;
use App\V1\Observers\CashboxUpdateObserver;
use App\V1\Models\Employee;
use App\V1\Models\Employee\Cashbox;
use App\V1\Observers\Audit\EmployeeAudit;
use App\V1\Observers\HrPortal\RegisterOnPortalObserver;

class EmployeeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/employee.php'));

        Employee::observe(RecordChangeObserver::class);
        Employee::observe(EmployeeAudit::class);
        Cashbox::observe(CashboxUpdateObserver::class);
        Employee::observe(RegisterOnPortalObserver::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\EmployeeRepository',
            'App\V1\Repositories\EmployeeRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\EmployeeFilter',
            'App\V1\Repositories\Query\EmployeeFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\EmployeeSorter',
            'App\V1\Repositories\Query\EmployeeSorter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Employee\OutclinicMedicineRepository',
            'App\V1\Repositories\Employee\OutclinicMedicineRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Employee\OutclinicMedicineFilter',
            'App\V1\Repositories\Query\Employee\OutclinicMedicineFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Employee\OutclinicMedicineSorter',
            'App\V1\Repositories\Query\Employee\OutclinicMedicineSorter'
        );

        $this->app->bind(
            'App\V1\Contracts\Services\HrPortalService',
            'App\V1\Services\HrPortalService'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Employee\OutclinicSpecializationRepository',
            'App\V1\Repositories\Employee\OutclinicSpecializationRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Employee\OutclinicSpecializationFilter',
            'App\V1\Repositories\Query\Employee\OutclinicSpecializationFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Employee\OutclinicSpecializationSorter',
            'App\V1\Repositories\Query\Employee\OutclinicSpecializationSorter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Employee\OutclinicDiagnosticRepository',
            'App\V1\Repositories\Employee\OutclinicDiagnosticRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Employee\OutclinicDiagnosticFilter',
            'App\V1\Repositories\Query\Employee\OutclinicDiagnosticFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Employee\OutclinicDiagnosticSorter',
            'App\V1\Repositories\Query\Employee\OutclinicDiagnosticSorter'
        );
    }
}
