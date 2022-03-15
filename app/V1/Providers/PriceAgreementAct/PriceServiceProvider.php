<?php

namespace App\V1\Providers\PriceAgreementAct;

use App\V1\Models\PriceAgreementAct;
use App\V1\Observers\Audit\PriceAgreementAct\PriceAudit;
use Illuminate\Support\ServiceProvider;
use App\V1\Models\PriceAgreementAct\Price;
use App\V1\Observers\Audit\PriceAgreementActAudit;

class PriceServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        Price::observe(PriceAudit::class);
        PriceAgreementAct::observe(PriceAgreementActAudit::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\PriceAgreementAct\PriceRepository',
            'App\V1\Repositories\PriceAgreementAct\PriceRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\PriceAgreementAct\PriceFilter',
            'App\V1\Repositories\Query\PriceAgreementAct\PriceFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\PriceAgreementAct\PriceSorter',
            'App\V1\Repositories\Query\PriceAgreementAct\PriceSorter'
        );
    }
}
