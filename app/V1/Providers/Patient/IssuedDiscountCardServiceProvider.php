<?php

namespace App\V1\Providers\Patient;

use Illuminate\Support\ServiceProvider;

class IssuedDiscountCardServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/patient/issued-discount-card.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Patient\IssuedDiscountCardRepository',
            'App\V1\Repositories\Patient\IssuedDiscountCardRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\IssuedDiscountCardFilter',
            'App\V1\Repositories\Query\Patient\IssuedDiscountCardFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\IssuedDiscountCardSorter',
            'App\V1\Repositories\Query\Patient\IssuedDiscountCardSorter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\IssuedDiscountCardScopes',
            'App\V1\Repositories\Query\Patient\IssuedDiscountCardScopes'
        );
    }
}