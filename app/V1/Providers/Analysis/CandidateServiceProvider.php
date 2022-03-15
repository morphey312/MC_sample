<?php

namespace App\V1\Providers\Analysis;

use Illuminate\Support\ServiceProvider;

class CandidateServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/analysis/candidate.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Analysis\CandidateRepository',
            'App\V1\Repositories\Analysis\CandidateRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Analysis\CandidateFilter',
            'App\V1\Repositories\Query\Analysis\CandidateFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Analysis\CandidateSorter',
            'App\V1\Repositories\Query\Analysis\CandidateSorter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Analysis\CandidateScopes',
            'App\V1\Repositories\Query\Analysis\CandidateScopes'
        );
    }
}