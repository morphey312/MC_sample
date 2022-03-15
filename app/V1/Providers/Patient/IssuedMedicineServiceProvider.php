<?php

namespace App\V1\Providers\Patient;

use App\V1\Models\Patient\IssuedMedicine;
use App\V1\Observers\Audit\IssuedMedicineAudit;
use Illuminate\Support\ServiceProvider;

class IssuedMedicineServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        IssuedMedicine::observe(IssuedMedicineAudit::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Patient\IssuedMedicineRepository',
            'App\V1\Repositories\Patient\IssuedMedicineRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\IssuedMedicineFilter',
            'App\V1\Repositories\Query\Patient\IssuedMedicineFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\IssuedMedicineSorter',
            'App\V1\Repositories\Query\Patient\IssuedMedicineSorter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\IssuedMedicineScopes',
            'App\V1\Repositories\Query\Patient\IssuedMedicineScopes'
        );
    }
}
