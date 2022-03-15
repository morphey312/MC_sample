<?php

namespace App\V1\Providers\DaySheet;

use Illuminate\Support\ServiceProvider;

class TimeBlockReasonServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/day-sheet/time-block-reasons.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\DaySheet\TimeBlockReasonRepository',
            'App\V1\Repositories\DaySheet\TimeBlockReasonRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\DaySheet\TimeBlockReasonFilter',
            'App\V1\Repositories\Query\DaySheet\TimeBlockReasonFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\DaySheet\TimeBlockReasonSorter',
            'App\V1\Repositories\Query\DaySheet\TimeBlockReasonSorter'
        );
    }
}
