<?php

namespace App\V1\Providers\Employee;

use Illuminate\Support\ServiceProvider;

class DocumentServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/employee/document.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Employee\DocumentRepository',
            'App\V1\Repositories\Employee\DocumentRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Employee\DocumentFilter',
            'App\V1\Repositories\Query\Employee\DocumentFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Employee\DocumentSorter',
            'App\V1\Repositories\Query\Employee\DocumentSorter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Employee\DocumentScopes',
            'App\V1\Repositories\Query\Employee\DocumentScopes'
        );
    }
}