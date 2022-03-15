<?php

namespace App\V1\Providers;

use App\V1\Observers\Audit\Clinic\ClinicAudit;
use App\V1\Observers\HrPortal\CreateClinicObserver;
use Illuminate\Support\ServiceProvider;
use App\V1\Observers\RecordChangeObserver;
use App\V1\Observers\Clinic\GroupSourceObserver;
use App\V1\Models\Clinic;

class ClinicServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/clinics.php'));

        Clinic::observe(RecordChangeObserver::class);
        Clinic::observe(GroupSourceObserver::class);
        Clinic::observe(ClinicAudit::class);
        Clinic::observe(CreateClinicObserver::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\ClinicRepository',
            'App\V1\Repositories\ClinicRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\ClinicFilter',
            'App\V1\Repositories\Query\ClinicFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\ClinicSorter',
            'App\V1\Repositories\Query\ClinicSorter'
        );
    }
}
