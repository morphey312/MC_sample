<?php

namespace App\V1\Providers\Analysis;

use Illuminate\Support\ServiceProvider;

class TemplateServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/analysis/template.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Analysis\TemplateRepository',
            'App\V1\Repositories\Analysis\TemplateRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Analysis\TemplateFilter',
            'App\V1\Repositories\Query\Analysis\TemplateFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Analysis\TemplateSorter',
            'App\V1\Repositories\Query\Analysis\TemplateSorter'
        );
    }
}
