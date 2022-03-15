<?php

namespace App\V1\Providers\Specialization;

use App\V1\Models\Specialization\Clinic;
use App\V1\Observers\Audit\Clinic\Specialization\ClinicAudit;
use Illuminate\Support\ServiceProvider;

class ClinicServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/specialization/clinic.php'));

        Clinic::observe(ClinicAudit::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Specialization\ClinicRepository',
            'App\V1\Repositories\Specialization\ClinicRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Specialization\ClinicFilter',
            'App\V1\Repositories\Query\Specialization\ClinicFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Specialization\ClinicSorter',
            'App\V1\Repositories\Query\Specialization\ClinicSorter'
        );
    }
}
