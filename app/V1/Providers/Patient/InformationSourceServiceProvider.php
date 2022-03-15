<?php

namespace App\V1\Providers\Patient;

use Illuminate\Support\ServiceProvider;
use App\V1\Observers\RecordChangeObserver;
use App\V1\Models\Patient\InformationSource;
use App\V1\Observers\Audit\Patient\InformationSourceAudit;

class InformationSourceServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/patient/information-source.php'));
        
        InformationSource::observe(RecordChangeObserver::class);
        InformationSource::observe(InformationSourceAudit::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Patient\InformationSourceRepository',
            'App\V1\Repositories\Patient\InformationSourceRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\InformationSourceFilter',
            'App\V1\Repositories\Query\Patient\InformationSourceFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\InformationSourceSorter',
            'App\V1\Repositories\Query\Patient\InformationSourceSorter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\InformationSourceScopes',
            'App\V1\Repositories\Query\Patient\InformationSourceScopes'
        );
    }
}