{!! "<"."?php" !!}

namespace App\{{ $version }}\Repositories\Query{{ $namespace ? "\\$namespace" : '' }};

use App\{{ $version }}\Contracts\Repositories\Query\{{ $fullName }}Filter as FilterInterface;
use App\{{ $version }}\Repositories\Query\BaseFilter;

class {{ $name }}Filter extends BaseFilter implements FilterInterface
{
    //
}