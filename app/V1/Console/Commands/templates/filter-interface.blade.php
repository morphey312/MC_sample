{!! "<"."?php" !!}

namespace App\{{ $version }}\Contracts\Repositories\Query{{ $namespace ? "\\$namespace" : '' }};

use App\{{ $version }}\Contracts\Repositories\Query\Filter;

interface {{ $name }}Filter extends Filter
{
}