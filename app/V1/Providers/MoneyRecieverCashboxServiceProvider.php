<?php

namespace App\V1\Providers;

use App\V1\Models\MoneyRecieverCashbox;
use App\V1\Observers\Audit\MoneyReciever\CashboxAudit;
use Illuminate\Support\ServiceProvider;

class MoneyRecieverCashboxServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/clinic/money-reciever/cashbox.php'));

        MoneyRecieverCashbox::observe(CashboxAudit::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\MoneyRecieverCashboxRepository',
            'App\V1\Repositories\MoneyRecieverCashboxRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\MoneyRecieverCashboxFilter',
            'App\V1\Repositories\Query\MoneyRecieverCashboxFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\MoneyRecieverCashboxSorter',
            'App\V1\Repositories\Query\MoneyRecieverCashboxSorter'
        );
    }
}
