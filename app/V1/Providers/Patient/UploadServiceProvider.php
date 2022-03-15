<?php

namespace App\V1\Providers\Patient;

use App\V1\Models\Patient\Upload;
use App\V1\Observers\Audit\Patient\UploadAudit;
use Illuminate\Support\ServiceProvider;

class UploadServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/patient/upload.php'));

        Upload::observe(UploadAudit::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Patient\UploadRepository',
            'App\V1\Repositories\Patient\UploadRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\UploadFilter',
            'App\V1\Repositories\Query\Patient\UploadFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\UploadSorter',
            'App\V1\Repositories\Query\Patient\UploadSorter'
        );
    }
}
