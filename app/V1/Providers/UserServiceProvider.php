<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;
use App\V1\Models\User;
use App\V1\Observers\Audit\UserAudit;

class UserServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/user.php'));
        
        User::observe(UserAudit::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\UserRepository',
            'App\V1\Repositories\UserRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\UserFilter',
            'App\V1\Repositories\Query\UserFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\UserSorter',
            'App\V1\Repositories\Query\UserSorter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\UserScopes',
            'App\V1\Repositories\Query\UserScopes'
        );
    }
}