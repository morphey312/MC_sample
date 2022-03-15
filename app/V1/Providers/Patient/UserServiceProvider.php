<?php

namespace App\V1\Providers\Patient;

use Illuminate\Support\ServiceProvider;

class UserServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/patient/user.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Patient\UserRepository',
            'App\V1\Repositories\Patient\UserRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\UserFilter',
            'App\V1\Repositories\Query\Patient\UserFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\UserSorter',
            'App\V1\Repositories\Query\Patient\UserSorter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\UserScopes',
            'App\V1\Repositories\Query\Patient\UserScopes'
        );
    }
}