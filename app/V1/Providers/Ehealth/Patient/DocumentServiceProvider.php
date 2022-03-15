<?php

namespace App\V1\Providers\Ehealth\Patient;

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
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Ehealth\Patient\DocumentRepository',
            'App\V1\Repositories\Ehealth\Patient\DocumentRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Ehealth\Patient\DocumentFilter',
            'App\V1\Repositories\Query\Ehealth\Patient\DocumentFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Ehealth\Patient\DocumentSorter',
            'App\V1\Repositories\Query\Ehealth\Patient\DocumentSorter'
        );
    }
}
