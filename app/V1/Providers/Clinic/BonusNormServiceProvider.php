<?php

namespace App\V1\Providers\Clinic;

use Illuminate\Support\ServiceProvider;
use App\V1\Observers\RecordChangeObserver;
use App\V1\Models\Clinic\BonusNorm;

class BonusNormServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/clinic/bonus-norm.php'));

        BonusNorm::observe(RecordChangeObserver::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Clinic\BonusNormRepository',
            'App\V1\Repositories\Clinic\BonusNormRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Clinic\BonusNormFilter',
            'App\V1\Repositories\Query\Clinic\BonusNormFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Clinic\BonusNormSorter',
            'App\V1\Repositories\Query\Clinic\BonusNormSorter'
        );
    }
}
