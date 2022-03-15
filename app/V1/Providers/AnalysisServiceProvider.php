<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;
use App\V1\Models\Analysis;
use App\V1\Observers\Audit\AnalysisAudit;
use App\V1\Observers\RecordChangeObserver;

class AnalysisServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/analysis.php'));

        Analysis::observe(RecordChangeObserver::class);
        Analysis::observe(AnalysisAudit::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\AnalysisRepository',
            'App\V1\Repositories\AnalysisRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\AnalysisFilter',
            'App\V1\Repositories\Query\AnalysisFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\AnalysisSorter',
            'App\V1\Repositories\Query\AnalysisSorter'
        );

        $this->app->bind(
            'App\V1\Contracts\Services\Merge\Analysis',
            'App\V1\Services\Merge\Analysis'
        );
    }
}
