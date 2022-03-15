<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;

class VoipServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Services\VoipService',
            'App\V1\Services\VoipService'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Services\Voip\MessageQueue',
            'App\V1\Services\Voip\MessageQueue'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Services\Voip\SubResolver',
            'App\V1\Services\Voip\SubResolver'
        );

        $this->app->bind(
            'voip', 
            'App\V1\Contracts\Services\VoipService'
        );
        
        $this->app->bind(
            'voipQueue', 
            'App\V1\Contracts\Services\Voip\MessageQueue'
        );
    }
}
