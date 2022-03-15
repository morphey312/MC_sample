<?php

namespace App\V1\Providers\Ehealth;

use Illuminate\Support\ServiceProvider;

class PackageRecordServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/ehealth/package-record.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Ehealth\PackageRecordRepository',
            'App\V1\Repositories\Ehealth\PackageRecordRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Ehealth\PackageRecordFilter',
            'App\V1\Repositories\Query\Ehealth\PackageRecordFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Ehealth\PackageRecordSorter',
            'App\V1\Repositories\Query\Ehealth\PackageRecordSorter'
        );
    }
}
