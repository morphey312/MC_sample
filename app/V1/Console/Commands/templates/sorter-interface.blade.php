{!! "<"."?php" !!}

namespace App\{{ $version }}\Contracts\Repositories\Query{{ $namespace ? "\\$namespace" : '' }};

use App\{{ $version }}\Contracts\Repositories\Query\Sorter;

interface {{ $name }}Sorter extends Sorter
{
}