<?php

namespace App\V1\Providers\Patient\Card;

use Illuminate\Support\ServiceProvider;

class TreatmentAssignmentServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/patient/card/treatment-assignment.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Patient\Card\TreatmentAssignmentRepository',
            'App\V1\Repositories\Patient\Card\TreatmentAssignmentRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\Card\TreatmentAssignmentFilter',
            'App\V1\Repositories\Query\Patient\Card\TreatmentAssignmentFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\Card\TreatmentAssignmentSorter',
            'App\V1\Repositories\Query\Patient\Card\TreatmentAssignmentSorter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\Card\TreatmentAssignmentScopes',
            'App\V1\Repositories\Query\Patient\Card\TreatmentAssignmentScopes'
        );
    }
}