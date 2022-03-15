<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Connection;
use App\V1\Repositories\MysqlConnection;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * @var array
     */
    protected $features = [
        Feature\SetupResource::class,
        Feature\BindListRequest::class,
        Feature\SetupRelations::class,
        Feature\QueryBuilderMacro::class,
        Feature\ExtendValidation::class,
    ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        URL::forceScheme('https');

        foreach ($this->features as $feature) {
            $feature::boot($this->app);
        }

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Connection::resolverFor('mysql', function ($connection, $database, $prefix, $config) {
            return new MysqlConnection($connection, $database, $prefix, $config);
        });
        $this->app->bind(
            'Illuminate\Notifications\Events\BroadcastNotificationCreated',
            'App\V1\Notifications\BaseBroadcastNotificationCreated'
        );
        $this->app->bind(
            'App\V1\Contracts\Services\MutexService',
            'App\V1\Services\MutexService'
        );

        $this->app->bind(
            'mutex',
            'App\V1\Contracts\Services\MutexService'
        );

        $this->app->bind(
            'App\V1\Contracts\Services\ClientService',
            'App\V1\Services\ClientService'
        );

        $this->app->bind(
            'client-manager',
            'App\V1\Contracts\Services\ClientService'
        );
    }
}


// fix broadcast()->toOthers "Invalid socket ID"
namespace Pusher;

function preg_match($pattern, $subject, &$matches = null, $flags = 0, $offset = 0)
{
    if ($pattern === '/\A\d+\.\d+\z/') {
        $pattern = '/[\w.]+/';
    }
    return \preg_match($pattern, $subject, $matches, $flags, $offset);
}
