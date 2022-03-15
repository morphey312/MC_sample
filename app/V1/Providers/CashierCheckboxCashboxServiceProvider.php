<?php

namespace App\V1\Providers;

use App\V1\Models\CashierCheckboxCashbox;
use App\V1\Observers\Audit\CashierCheckboxCashboxAudit;
use Illuminate\Support\ServiceProvider;

class CashierCheckboxCashboxServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/employee/checkbox-credentials.php'));

        CashierCheckboxCashbox::observe(CashierCheckboxCashboxAudit::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\CashierCheckboxCashboxRepository',
            'App\V1\Repositories\CashierCheckboxCashboxRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\CashierCheckboxCashboxFilter',
            'App\V1\Repositories\Query\CashierCheckboxCashboxFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\CashierCheckboxCashboxSorter',
            'App\V1\Repositories\Query\CashierCheckboxCashboxSorter'
        );
    }
}
