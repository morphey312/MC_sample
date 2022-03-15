<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;

class OneSServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Services\OneS\ImportClientService',
            'App\V1\Services\OneS\ImportClientService'
        );

        $this->app->bind(
            'oneSImportClient',
            'App\V1\Contracts\Services\OneS\ImportClientService'
        );

        $this->app->bind(
            'App\V1\Contracts\Services\OneS\TransactionService',
            'App\V1\Services\OneS\TransactionService'
        );

        $this->app->bind(
            'oneSTransaction',
            'App\V1\Contracts\Services\OneS\TransactionService'
        );

        $this->app->bind(
            'App\V1\Contracts\Services\OneS\MedicineIssueService',
            'App\V1\Services\OneS\MedicineIssueService'
        );

        $this->app->bind(
            'oneSMedicineIssue',
            'App\V1\Contracts\Services\OneS\MedicineIssueService'
        );
    }
}
