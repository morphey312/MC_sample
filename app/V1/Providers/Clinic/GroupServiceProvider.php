<?php

namespace App\V1\Providers\Clinic;

use App\V1\Models\Clinic\Group;
use App\V1\Observers\Audit\Clinic\Clinic\GroupAudit;
use Illuminate\Support\ServiceProvider;

class GroupServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/clinic/group.php'));

        Group::observe(GroupAudit::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Clinic\GroupRepository',
            'App\V1\Repositories\Clinic\GroupRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Clinic\GroupFilter',
            'App\V1\Repositories\Query\Clinic\GroupFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Clinic\GroupSorter',
            'App\V1\Repositories\Query\Clinic\GroupSorter'
        );
    }
}
