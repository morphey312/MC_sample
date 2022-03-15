<?php

namespace App\V1\Providers\Ehealth;

use Illuminate\Support\ServiceProvider;

class CareEpisodeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/ehealth/care-episode.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Ehealth\CareEpisodeRepository',
            'App\V1\Repositories\Ehealth\CareEpisodeRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Ehealth\CareEpisodeFilter',
            'App\V1\Repositories\Query\Ehealth\CareEpisodeFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Ehealth\CareEpisodeSorter',
            'App\V1\Repositories\Query\Ehealth\CareEpisodeSorter'
        );
    }
}
