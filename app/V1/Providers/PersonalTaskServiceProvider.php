<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;
use App\V1\Models\PersonalTask;
use App\V1\Observers\PersonalTaskObserver;

class PersonalTaskServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/personal-task.php'));

        PersonalTask::observe(PersonalTaskObserver::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\PersonalTaskRepository',
            'App\V1\Repositories\PersonalTaskRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\PersonalTaskFilter',
            'App\V1\Repositories\Query\PersonalTaskFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\PersonalTaskSorter',
            'App\V1\Repositories\Query\PersonalTaskSorter'
        );
    }
}
