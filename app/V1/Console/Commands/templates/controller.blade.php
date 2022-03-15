{!! "<"."?php" !!}

namespace App\{{ $version }}\Http\Controllers{{ $namespace ? "\\$namespace" : '' }};

use App\{{ $version }}\Http\Controllers\BaseResourceController;
use App\{{ $version }}\Models\{{ $fullName }};
use App\{{ $version }}\Contracts\Repositories\{{ $fullName }}Repository;
use App\{{ $version }}\Contracts\Repositories\Query\{{ $fullName }}Filter;
use App\{{ $version }}\Contracts\Repositories\Query\{{ $fullName }}Sorter;
use App\{{ $version }}\Http\Requests\{{ $fullName }}Request;
use App\{{ $version }}\Http\Resources\{{ $fullName }}Resource;

class {{ $name }}Controller extends BaseResourceController
{
    /**
     * @var string
     */
    protected $modelClass = {{ $name }}::class;

    /**
     * @var string
     */
    protected $repositoryClass = {{ $name }}Repository::class;

    /**
     * @var string
     */
    protected $filterClass = {{ $name }}Filter::class;

    /**
     * @var string
     */
    protected $sorterClass = {{ $name }}Sorter::class;

    /**
     * @var string
     */
    protected $requestClass = {{ $name }}Request::class;

    /**
     * @var string
     */
    protected $resourceClass = {{ $name }}Resource::class;
}