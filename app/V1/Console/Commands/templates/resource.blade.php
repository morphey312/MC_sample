{!! "<"."?php" !!}

namespace App\{{ $version }}\Http\Resources{{ $namespace ? "\\$namespace" : '' }};

use App\{{ $version }}\Http\Resources\ScopedResource;

class {{ $name }}Resource extends ScopedResource
{
    /**
     * Transform the resource into an array
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
        ];
    }
}