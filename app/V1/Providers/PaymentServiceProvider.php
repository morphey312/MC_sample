<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;
use App\V1\Models\Payment;
use App\V1\Observers\Audit\PaymentAudit;
use App\V1\Observers\RecordChangeObserver;
use App\V1\Observers\PaymentObserver;
use App\V1\Observers\Elastic\PaymentObserver as ElasticPaymentObserver;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/payment.php'));

        Payment::observe(RecordChangeObserver::class);
        Payment::observe(PaymentAudit::class);
        Payment::observe(PaymentObserver::class);
        Payment::observe(ElasticPaymentObserver::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\PaymentRepository',
            'App\V1\Repositories\PaymentRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\PaymentFilter',
            'App\V1\Repositories\Query\PaymentFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\PaymentSorter',
            'App\V1\Repositories\Query\PaymentSorter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\PaymentScopes',
            'App\V1\Repositories\Query\PaymentScopes'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Employee\CashboxRepository',
            'App\V1\Repositories\Employee\CashboxRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Services\OnlinePaymentService',
            'App\V1\Services\OnlinePaymentService'
        );

        $this->app->bind(
            'onlinePaymentService',
            'App\V1\Contracts\Services\OnlinePaymentService'
        );
    }
}