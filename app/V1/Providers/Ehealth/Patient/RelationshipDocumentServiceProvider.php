<?php

namespace App\V1\Providers\Ehealth\Patient;

use Illuminate\Support\ServiceProvider;

class RelationshipDocumentServiceProvider extends ServiceProvider
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
            'App\V1\Contracts\Repositories\Ehealth\Patient\RelationshipDocumentRepository',
            'App\V1\Repositories\Ehealth\Patient\RelationshipDocumentRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Ehealth\Patient\RelationshipDocumentFilter',
            'App\V1\Repositories\Query\Ehealth\Patient\RelationshipDocumentFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Ehealth\Patient\RelationshipDocumentSorter',
            'App\V1\Repositories\Query\Ehealth\Patient\RelationshipDocumentSorter'
        );
    }
}
