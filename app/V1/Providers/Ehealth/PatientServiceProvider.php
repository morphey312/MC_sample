<?php

namespace App\V1\Providers\Ehealth;

use Illuminate\Support\ServiceProvider;
use App\V1\Models\Patient;
use App\V1\Observers\PatientObserver;
use App\V1\Observers\Audit\PatientAudit;
use App\V1\Observers\RecordChangeObserver;
use App\V1\Observers\Elastic\PatientAppointmentObserver;

class PatientServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/ehealth/patient.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Ehealth\PatientRepository',
            'App\V1\Repositories\Ehealth\PatientRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Ehealth\PatientFilter',
            'App\V1\Repositories\Query\Ehealth\PatientFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Ehealth\PatientSorter',
            'App\V1\Repositories\Query\Ehealth\PatientSorter'
        );
    }
}
