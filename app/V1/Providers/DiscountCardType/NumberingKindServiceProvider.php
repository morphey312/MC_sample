<?php

namespace App\V1\Providers\DiscountCardType;

use Illuminate\Support\ServiceProvider;
use App\V1\Observers\RecordChangeObserver;
use App\V1\Models\DiscountCardType\NumberingKind;

class NumberingKindServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/discount-card-type/numbering-kind.php'));

        NumberingKind::observe(RecordChangeObserver::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\DiscountCardType\NumberingKindRepository',
            'App\V1\Repositories\DiscountCardType\NumberingKindRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\DiscountCardType\NumberingKindFilter',
            'App\V1\Repositories\Query\DiscountCardType\NumberingKindFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\DiscountCardType\NumberingKindSorter',
            'App\V1\Repositories\Query\DiscountCardType\NumberingKindSorter'
        );
    }
}
