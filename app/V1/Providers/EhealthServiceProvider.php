<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;

class EhealthServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/employee/speciality-type.php'));
        $this->loadRoutesFrom(base_path('routes/modules/v1/ehealth/position.php'));
        $this->loadRoutesFrom(base_path('routes/modules/v1/ehealth/encounter/handbook/reason.php'));
        $this->loadRoutesFrom(base_path('routes/modules/v1/ehealth/encounter/handbook/discharge-department.php'));
        $this->loadRoutesFrom(base_path('routes/modules/v1/ehealth/service.php'));
        $this->loadRoutesFrom(base_path('routes/modules/v1/ehealth/application.php'));
        $this->loadRoutesFrom(base_path('routes/modules/v1/ehealth.php'));
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Services\EhealthService',
            'App\V1\Services\EhealthService'
        );


        $this->app->bind(
            'App\V1\Contracts\Repositories\Employee\SpecialityTypeRepository',
            'App\V1\Repositories\Employee\SpecialityTypeRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Employee\SpecialityTypeFilter',
            'App\V1\Repositories\Query\Employee\SpecialityTypeFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Employee\SpecialityTypeSorter',
            'App\V1\Repositories\Query\Employee\SpecialityTypeSorter'
        );


        $this->app->bind(
            'App\V1\Contracts\Repositories\Ehealth\ApplicationRepository',
            'App\V1\Repositories\Ehealth\ApplicationRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Ehealth\ApplicationFilter',
            'App\V1\Repositories\Query\Ehealth\ApplicationFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Ehealth\ApplicationSorter',
            'App\V1\Repositories\Query\Ehealth\ApplicationSorter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Ehealth\PositionRepository',
            'App\V1\Repositories\Ehealth\PositionRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Ehealth\PositionFilter',
            'App\V1\Repositories\Query\Ehealth\PositionFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Ehealth\PositionSorter',
            'App\V1\Repositories\Query\Ehealth\PositionSorter'
        );


        $this->app->bind(
            'App\V1\Contracts\Repositories\Ehealth\ServiceRepository',
            'App\V1\Repositories\Ehealth\ServiceRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Ehealth\ServiceFilter',
            'App\V1\Repositories\Query\Ehealth\ServiceFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Ehealth\ServiceSorter',
            'App\V1\Repositories\Query\Ehealth\ServiceSorter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Ehealth\Encounter\HandBook\ReasonRepository',
            'App\V1\Repositories\Ehealth\Encounter\HandBook\ReasonRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Ehealth\Encounter\HandBook\ReasonFilter',
            'App\V1\Repositories\Query\Ehealth\Encounter\HandBook\ReasonFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Ehealth\Encounter\HandBook\ReasonSorter',
            'App\V1\Repositories\Query\Ehealth\Encounter\HandBook\ReasonSorter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Ehealth\Encounter\HandBook\DischargeDepartmentRepository',
            'App\V1\Repositories\Ehealth\Encounter\HandBook\DischargeDepartmentRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Ehealth\Encounter\HandBook\DischargeDepartmentFilter',
            'App\V1\Repositories\Query\Ehealth\Encounter\HandBook\DischargeDepartmentFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Ehealth\Encounter\HandBook\DischargeDepartmentSorter',
            'App\V1\Repositories\Query\Ehealth\Encounter\HandBook\DischargeDepartmentSorter'
        );
    }
}
