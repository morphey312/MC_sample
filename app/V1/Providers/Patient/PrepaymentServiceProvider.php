<?php

namespace App\V1\Providers\Patient;

use Illuminate\Support\ServiceProvider;
use App\V1\Models\Patient\Prepayment;
use App\V1\Observers\Audit\Patient\PrepaymentAudit;

class PrepaymentServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/patient/prepayment.php'));

        Prepayment::observe(PrepaymentAudit::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Patient\PrepaymentRepository',
            'App\V1\Repositories\Patient\PrepaymentRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\PrepaymentFilter',
            'App\V1\Repositories\Query\Patient\PrepaymentFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\PrepaymentSorter',
            'App\V1\Repositories\Query\Patient\PrepaymentSorter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\PrepaymentScopes',
            'App\V1\Repositories\Query\Patient\PrepaymentScopes'
        );
    }
}