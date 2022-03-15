<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;

class FileAttachmentServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/file-attachment.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\FileAttachmentRepository',
            'App\V1\Repositories\FileAttachmentRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\FileAttachmentFilter',
            'App\V1\Repositories\Query\FileAttachmentFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\FileAttachmentSorter',
            'App\V1\Repositories\Query\FileAttachmentSorter'
        );
    }
}