<?php

namespace App\V1\Providers\Clinic;

use App\V1\Models\Clinic\MoneyReciever;
use App\V1\Observers\Audit\Clinic\Clinic\MoneyRecieverAudit;
use Illuminate\Support\ServiceProvider;

class MoneyRecieverServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/clinic/money-reciever.php'));

        MoneyReciever::observe(MoneyRecieverAudit::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Clinic\MoneyRecieverRepository',
            'App\V1\Repositories\Clinic\MoneyRecieverRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Clinic\MoneyRecieverFilter',
            'App\V1\Repositories\Query\Clinic\MoneyRecieverFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Clinic\MoneyRecieverSorter',
            'App\V1\Repositories\Query\Clinic\MoneyRecieverSorter'
        );
    }
}
