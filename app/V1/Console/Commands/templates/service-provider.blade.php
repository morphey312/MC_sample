{!! "<"."?php" !!}

namespace App\{{ $version }}\Providers{{ $namespace ? "\\$namespace" : '' }};

use Illuminate\Support\ServiceProvider;

class {{ $name }}ServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register services
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            'App\{{ $version }}\Contracts\Repositories\{{ $fullName }}Repository',
            'App\{{ $version }}\Repositories\{{ $fullName }}Repository'
        );
        
        $this->app->bind(
            'App\{{ $version }}\Contracts\Repositories\Query\{{ $fullName }}Filter',
            'App\{{ $version }}\Repositories\Query\{{ $fullName }}Filter'
        );
        
        $this->app->bind(
            'App\{{ $version }}\Contracts\Repositories\Query\{{ $fullName }}Sorter',
            'App\{{ $version }}\Repositories\Query\{{ $fullName }}Sorter'
        );
    }
}