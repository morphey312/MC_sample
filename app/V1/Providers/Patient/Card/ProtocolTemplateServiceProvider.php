<?php

namespace App\V1\Providers\Patient\Card;

use Illuminate\Support\ServiceProvider;
use App\V1\Observers\RecordChangeObserver;
use App\V1\Models\Patient\Card\ProtocolTemplate;

class ProtocolTemplateServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/patient/card/protocol-template.php'));
        
        ProtocolTemplate::observe(RecordChangeObserver::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Patient\Card\ProtocolTemplateRepository',
            'App\V1\Repositories\Patient\Card\ProtocolTemplateRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\Card\ProtocolTemplateFilter',
            'App\V1\Repositories\Query\Patient\Card\ProtocolTemplateFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\Card\ProtocolTemplateSorter',
            'App\V1\Repositories\Query\Patient\Card\ProtocolTemplateSorter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\Card\ProtocolTemplateScopes',
            'App\V1\Repositories\Query\Patient\Card\ProtocolTemplateScopes'
        );
    }
}