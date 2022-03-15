{!! "<"."?php" !!}

namespace App\{{ $version }}\Repositories{{ $namespace ? "\\$namespace" : '' }};

use App\{{ $version }}\Contracts\Repositories\{{ $fullName }}Repository as RepositoryInterface;
use App\{{ $version }}\Repositories\BaseRepository;
use App\{{ $version }}\Models\{{ $fullName }};
use App\V1\Contracts\Repositories\Query\{{ $fullName }}Filter;
use App\V1\Contracts\Repositories\Query\{{ $fullName }}Sorter;

class {{ $name }}Repository extends BaseRepository implements RepositoryInterface
{
    /**
     * @inherit
     */
    protected function query()
    {
        return {{ $name }}::query();
    }
    
    /**
     * @inherit
     */
    public function filter(array $filters = [])
    {
        return $this->makeFilter({{ $name }}Filter::class, $filters);
    }
    
    /**
     * @inherit
     */
    public function sorter(array $order = [])
    {
        return $this->makeSorter({{ $name }}Sorter::class, $order);
    }
}