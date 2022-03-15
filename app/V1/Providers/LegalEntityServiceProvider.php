<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;

class LegalEntityServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/legal-entity.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\LegalEntityRepository',
            'App\V1\Repositories\LegalEntityRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\LegalEntityFilter',
            'App\V1\Repositories\Query\LegalEntityFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\LegalEntitySorter',
            'App\V1\Repositories\Query\LegalEntitySorter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\LegalEntityScopes',
            'App\V1\Repositories\Query\LegalEntityScopes'
        );
    }
}