<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;

class ElasticsearhServiceProvider extends ServiceProvider
{
    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Services\ElasticsearchClientService',
            'App\V1\Services\ElasticsearchClientService'
        );

        $this->app->bind(
            'ElasticsearchClient',
            'App\V1\Contracts\Services\ElasticsearchClientService'
        );
    }
}