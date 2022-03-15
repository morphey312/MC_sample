<?php

namespace App\V1\Providers\Service;

use Illuminate\Support\ServiceProvider;
use App\V1\Observers\RecordChangeObserver;
use App\V1\Models\Service\PaymentDestination;

class PaymentDestinationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/service/payment_destination.php'));
        
        PaymentDestination::observe(RecordChangeObserver::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Service\PaymentDestinationRepository',
            'App\V1\Repositories\Service\PaymentDestinationRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Service\PaymentDestinationFilter',
            'App\V1\Repositories\Query\Service\PaymentDestinationFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Service\PaymentDestinationSorter',
            'App\V1\Repositories\Query\Service\PaymentDestinationSorter'
        );
    }
}