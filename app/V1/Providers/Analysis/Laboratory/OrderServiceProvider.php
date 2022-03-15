<?php

namespace App\V1\Providers\Analysis\Laboratory;

use Illuminate\Support\ServiceProvider;
use App\V1\Models\Analysis\Laboratory\TransferSheet;
use App\V1\Models\Analysis\Laboratory\Order\Item;
use App\V1\Observers\Analysis\Laboratory\TransferSheetObserver;
use App\V1\Observers\Audit\Analysis\Laboratory\RegistrationBiomaterialAudit;

class OrderServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/analysis/laboratory/order.php'));
        TransferSheet::observe(TransferSheetObserver::class);
        Item::observe(RegistrationBiomaterialAudit::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Analysis\Laboratory\Order\ItemRepository',
            'App\V1\Repositories\Analysis\Laboratory\Order\ItemRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Analysis\Laboratory\Order\ItemFilter',
            'App\V1\Repositories\Query\Analysis\Laboratory\Order\ItemFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Analysis\Laboratory\Order\ItemSorter',
            'App\V1\Repositories\Query\Analysis\Laboratory\Order\ItemSorter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Analysis\Laboratory\Order\Item\ContainerRepository',
            'App\V1\Repositories\Analysis\Laboratory\Order\Item\ContainerRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Analysis\Laboratory\Order\Item\ContainerFilter',
            'App\V1\Repositories\Query\Analysis\Laboratory\Order\Item\ContainerFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Analysis\Laboratory\Order\Item\ContainerSorter',
            'App\V1\Repositories\Query\Analysis\Laboratory\Order\Item\ContainerSorter'
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
