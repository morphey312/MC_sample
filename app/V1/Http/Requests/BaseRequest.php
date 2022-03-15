<?php

namespace App\V1\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class BaseRequest extends FormRequest
{
    /**
     * @inherit
     */
    public function authorize()
    {
        return true;
    }
    
    /**
     * Get only safe data from request to create/update model
     * 
     * @return array
     */ 
    public function safe()
    {
        return $this->all();
    }
}
