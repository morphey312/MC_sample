<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;

class PatientDocumentServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/patient-documents.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Handbook\PatientDocumentRepository',
            'App\V1\Repositories\Patient\PatientDocumentRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\PatientDocumentFilter',
            'App\V1\Repositories\Query\Patient\PatientDocumentFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\PatientDocumentSorter',
            'App\V1\Repositories\Query\Patient\PatientDocumentSorter'
        );
    }
}
