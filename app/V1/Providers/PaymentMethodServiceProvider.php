<?php

namespace App\V1\Providers;

use App\V1\Observers\Audit\PaymentMethodAudit;
use Illuminate\Support\ServiceProvider;
use App\V1\Observers\RecordChangeObserver;
use App\V1\Models\PaymentMethod;

class PaymentMethodServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/payment-method.php'));

        PaymentMethod::observe(RecordChangeObserver::class);
        PaymentMethod::observe(PaymentMethodAudit::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\PaymentMethodRepository',
            'App\V1\Repositories\PaymentMethodRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\PaymentMethodFilter',
            'App\V1\Repositories\Query\PaymentMethodFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\PaymentMethodSorter',
            'App\V1\Repositories\Query\PaymentMethodSorter'
        );
    }
}