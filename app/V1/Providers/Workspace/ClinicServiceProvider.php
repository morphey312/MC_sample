<?php

namespace App\V1\Providers\Workspace;

use App\V1\Models\Workspace\Clinic;
use App\V1\Observers\Audit\Workspace\ClinicAudit;
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
        $this->loadRoutesFrom(base_path('routes/modules/v1/workspace/clinic.php'));

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
            'App\V1\Contracts\Repositories\Workspace\ClinicRepository',
            'App\V1\Repositories\Workspace\ClinicRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Workspace\ClinicFilter',
            'App\V1\Repositories\Query\Workspace\ClinicFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Workspace\ClinicSorter',
            'App\V1\Repositories\Query\Workspace\ClinicSorter'
        );
    }
}
