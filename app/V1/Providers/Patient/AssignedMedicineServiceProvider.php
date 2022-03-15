<?php

namespace App\V1\Providers\Patient;

use App\V1\Models\Patient\AssignedMedicine;
use App\V1\Observers\Audit\AssignedMedicineAudit;
use Illuminate\Support\ServiceProvider;

class AssignedMedicineServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/patient/assigned-medicine.php'));

        AssignedMedicine::observe(AssignedMedicineAudit::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Patient\AssignedMedicineRepository',
            'App\V1\Repositories\Patient\AssignedMedicineRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\AssignedMedicineFilter',
            'App\V1\Repositories\Query\Patient\AssignedMedicineFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\AssignedMedicineSorter',
            'App\V1\Repositories\Query\Patient\AssignedMedicineSorter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\AssignedMedicineScopes',
            'App\V1\Repositories\Query\Patient\AssignedMedicineScopes'
        );
    }
}
