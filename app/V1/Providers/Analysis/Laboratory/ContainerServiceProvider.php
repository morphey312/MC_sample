<?php

namespace App\V1\Providers\Analysis\Laboratory;

use Illuminate\Support\ServiceProvider;
use App\V1\Observers\Analysis\Laboratory\TransferSheetObserver;
use App\V1\Observers\Audit\Analysis\Laboratory\RegistrationBiomaterialAudit;
use App\V1\Models\Analysis\Laboratory\TransferSheet;

class ContainerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/analysis/laboratory/container.php'));
        TransferSheet::observe(TransferSheetObserver::class);
        //@TODO FOR WHAT?  Item::observe(RegistrationBiomaterialAudit::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Analysis\Laboratory\ContainerRepository',
            'App\V1\Repositories\Analysis\Laboratory\ContainerRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Analysis\Laboratory\ContainerFilter',
            'App\V1\Repositories\Query\Analysis\Laboratory\ContainerFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Analysis\Laboratory\ContainerSorter',
            'App\V1\Repositories\Query\Analysis\Laboratory\ContainerSorter'
        );
        $this->app->bind(
            'App\V1\Contracts\Repositories\Analysis\Laboratory\TransferSheetRepository',
            'App\V1\Repositories\Analysis\Laboratory\TransferSheetRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Analysis\Laboratory\TransferSheetFilter',
            'App\V1\Repositories\Query\Analysis\Laboratory\TransferSheetFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Analysis\Laboratory\TransferSheetSorter',
            'App\V1\Repositories\Query\Analysis\Laboratory\TransferSheetSorter'
        );
    }
}