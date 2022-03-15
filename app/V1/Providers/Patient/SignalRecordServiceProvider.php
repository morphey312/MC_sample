<?php

namespace App\V1\Providers\Patient;

use Illuminate\Support\ServiceProvider;
use App\V1\Models\Patient\SignalRecord;
use App\V1\Observers\Audit\Patient\SignalRecordAudit;

class SignalRecordServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/patient/signal-record.php'));
        
        SignalRecord::observe(SignalRecordAudit::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Patient\SignalRecordRepository',
            'App\V1\Repositories\Patient\SignalRecordRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\SignalRecordFilter',
            'App\V1\Repositories\Query\Patient\SignalRecordFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\SignalRecordSorter',
            'App\V1\Repositories\Query\Patient\SignalRecordSorter'
        );
    }
}