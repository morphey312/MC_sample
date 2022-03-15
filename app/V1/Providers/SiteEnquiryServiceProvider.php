<?php

namespace App\V1\Providers;

use App\V1\Observers\Audit\SiteEnquiryAudit;
use Illuminate\Support\ServiceProvider;
use App\V1\Models\SiteEnquiry;
use App\V1\Observers\SiteEnquiryObserver;

class SiteEnquiryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/site-enquiry.php'));

        SiteEnquiry::observe(SiteEnquiryObserver::class);
        SiteEnquiry::observe(SiteEnquiryAudit::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\SiteEnquiryRepository',
            'App\V1\Repositories\SiteEnquiryRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\SiteEnquiryFilter',
            'App\V1\Repositories\Query\SiteEnquiryFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\SiteEnquirySorter',
            'App\V1\Repositories\Query\SiteEnquirySorter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\SiteEnquiryScopes',
            'App\V1\Repositories\Query\SiteEnquiryScopes'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\SiteEnquiry\ServiceRepository',
            'App\V1\Repositories\SiteEnquiry\ServiceRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\SiteEnquiry\ServiceFilter',
            'App\V1\Repositories\Query\SiteEnquiry\ServiceFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\SiteEnquiry\ServiceSorter',
            'App\V1\Repositories\Query\SiteEnquiry\ServiceSorter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\SiteEnquiry\ServiceScopes',
            'App\V1\Repositories\Query\SiteEnquiry\ServiceScopes'
        );
    }
}
