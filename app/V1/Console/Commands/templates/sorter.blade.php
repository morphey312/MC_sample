{!! "<"."?php" !!}

namespace App\{{ $version }}\Repositories\Query{{ $namespace ? "\\$namespace" : '' }};

use App\{{ $version }}\Contracts\Repositories\Query\{{ $fullName }}Sorter as SorterInterface;
use App\{{ $version }}\Repositories\Query\BaseSorter;

class {{ $name }}Sorter extends BaseSorter implements SorterInterface
{
    //
}