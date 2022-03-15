<?php

namespace App\V1\Providers;

use App\V1\Observers\Audit\Clinic\SpecializationAudit;
use Illuminate\Support\ServiceProvider;
use App\V1\Models\Specialization;
use App\V1\Observers\RecordChangeObserver;

class SpecializationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/specialization.php'));

        Specialization::observe(SpecializationAudit::class);
        Specialization::observe(RecordChangeObserver::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\SpecializationRepository',
            'App\V1\Repositories\SpecializationRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\SpecializationFilter',
            'App\V1\Repositories\Query\SpecializationFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\SpecializationSorter',
            'App\V1\Repositories\Query\SpecializationSorter'
        );
    }
}
