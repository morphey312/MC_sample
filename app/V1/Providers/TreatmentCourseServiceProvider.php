<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;

class TreatmentCourseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/treatment-course.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\TreatmentCourseRepository',
            'App\V1\Repositories\TreatmentCourseRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\TreatmentCourseFilter',
            'App\V1\Repositories\Query\TreatmentCourseFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\TreatmentCourseSorter',
            'App\V1\Repositories\Query\TreatmentCourseSorter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\TreatmentCourse\DocumentRepository',
            'App\V1\Repositories\TreatmentCourse\DocumentRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\TreatmentCourse\DocumentFilter',
            'App\V1\Repositories\Query\TreatmentCourse\DocumentFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\TreatmentCourse\DocumentSorter',
            'App\V1\Repositories\Query\TreatmentCourse\DocumentSorter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\TreatmentCourse\Document\SignatureRepository',
            'App\V1\Repositories\TreatmentCourse\Document\SignatureRepository'
        );
    }
}
