<?php

namespace App\V1\Providers\Patient\Card;

use Illuminate\Support\ServiceProvider;

class RecordTemplateServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/patient/card/record-template.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Patient\Card\RecordTemplateRepository',
            'App\V1\Repositories\Patient\Card\RecordTemplateRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\Card\RecordTemplateFilter',
            'App\V1\Repositories\Query\Patient\Card\RecordTemplateFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\Card\RecordTemplateSorter',
            'App\V1\Repositories\Query\Patient\Card\RecordTemplateSorter'
        );
        
        $this->app->bind(
            'form-template-compiler', 
            'App\V1\Contracts\Services\FormTemplateCompiler'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Services\FormTemplateCompiler', 
            'App\V1\Services\FormTemplateCompiler'
        );
    }
}