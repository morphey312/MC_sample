<?php

namespace App\V1\Providers;

use App\V1\Services\Esputnik;
use Illuminate\Support\ServiceProvider;

class EsputnikServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('esputnik', function($app){
            return new Esputnik();
        });

        $this->app->bind(
            'sender',
            'App\V1\Contracts\Services\Esputnik\Sender'
        );
    }
}
