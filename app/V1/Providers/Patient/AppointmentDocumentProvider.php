<?php


namespace App\V1\Providers\Patient;


use Illuminate\Support\ServiceProvider;

class AppointmentDocumentProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/patient/appointment-document.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Patient\AppointmentDocumentRepository',
            'App\V1\Repositories\Patient\AppointmentDocumentRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\AppointmentDocumentFilter',
            'App\V1\Repositories\Query\Patient\AppointmentDocumentFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\AppointmentDocumentSorter',
            'App\V1\Repositories\Query\Patient\AppointmentDocumentSorter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\AppointmentDocumentScopes',
            'App\V1\Repositories\Query\Patient\AppointmentDocumentScopes'
        );
    }
}
