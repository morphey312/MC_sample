{!! "<"."?php" !!}

namespace App\{{ $version }}\Http\Requests{{ $namespace ? "\\$namespace" : '' }};

use App\{{ $version }}\Http\Requests\BaseRequest;
use App\{{ $version }}\Models\{{ $fullName }};

class {{ $name }}Request extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }
    
    /**
     * Get safe data from request
     *
     * @return array
     */
    public function safe()
    {
        return $this->only((new {{ $name }})->getFillable());
    }
}
