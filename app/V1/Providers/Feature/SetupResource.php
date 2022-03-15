<?php

namespace App\V1\Providers\Feature;

use Illuminate\Http\Resources\Json\Resource;

class SetupResource
{
    /**
     * Boot feature
     * 
     * @param \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application $app
     */ 
    public static function boot($app) 
    {
        Resource::withoutWrapping();
    }
}