<?php

namespace App\V1\Providers\Appointment;

use Illuminate\Support\ServiceProvider;

class NoteServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/appointment/note.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Appointment\NoteRepository',
            'App\V1\Repositories\Appointment\NoteRepository'
        );
    }
}
