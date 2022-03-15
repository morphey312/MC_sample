{!! "<"."?php" !!}

namespace App\{{ $version }}\Contracts\Repositories{{ $namespace ? "\\$namespace" : '' }};

use App\{{ $version }}\Contracts\Repositories\BaseRepository;

interface {{ $name }}Repository extends BaseRepository
{
}