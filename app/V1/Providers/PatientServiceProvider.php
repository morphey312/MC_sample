<?php

namespace App\V1\Providers;

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
        $this->loadRoutesFrom(base_path('routes/modules/v1/patient.php'));

        Patient::observe(PatientObserver::class);
        Patient::observe(RecordChangeObserver::class);
        Patient::observe(PatientAudit::class);
        Patient::observe(PatientAppointmentObserver::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\PatientRepository',
            'App\V1\Repositories\PatientRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\PatientFilter',
            'App\V1\Repositories\Query\PatientFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\PatientSorter',
            'App\V1\Repositories\Query\PatientSorter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\PatientScopes',
            'App\V1\Repositories\Query\PatientScopes'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Appointment\Service\DebtFilter',
            'App\V1\Repositories\Query\Appointment\Service\DebtFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Services\Merge\Patient',
            'App\V1\Services\Merge\Patient'
        );
    }
}
