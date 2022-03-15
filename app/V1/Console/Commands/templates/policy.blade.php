{!! "<"."?php" !!}

namespace App\{{ $version }}\Policies{{ $namespace ? "\\$namespace" : '' }};

use App\{{ $version }}\Policies\BasePolicy;

class {{ $name }}Policy extends BasePolicy
{
    /**
     * @var string
     */ 
    protected $module = '{{ Illuminate\Support\Str::plural(Illuminate\Support\Str::snake($name)) }}';
}
