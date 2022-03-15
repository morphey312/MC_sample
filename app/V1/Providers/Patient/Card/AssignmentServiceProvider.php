<?php

namespace App\V1\Providers\Patient\Card;

use App\V1\Models\Patient\Card;
use App\V1\Observers\Audit\Patient\AssignmentAudit;
use Illuminate\Support\ServiceProvider;

class AssignmentServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/patient/card/assignment.php'));

        Card\Record::observe(AssignmentAudit::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Patient\Card\AssignmentRepository',
            'App\V1\Repositories\Patient\Card\AssignmentRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\Card\AssignmentFilter',
            'App\V1\Repositories\Query\Patient\Card\AssignmentFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\Card\AssignmentSorter',
            'App\V1\Repositories\Query\Patient\Card\AssignmentSorter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\Card\AssignmentScopes',
            'App\V1\Repositories\Query\Patient\Card\AssignmentScopes'
        );
    }
}
