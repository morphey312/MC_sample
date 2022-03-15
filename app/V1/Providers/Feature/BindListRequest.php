<?php

namespace App\V1\Providers\Feature;

use App\V1\Http\Requests\ListRequest;

class BindListRequest
{
    /**
     * Boot feature
     * 
     * @param \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application $app
     */
    public static function boot($app) 
    {
        $app->resolving(ListRequest::class, function ($request, $app) {
            ListRequest::createFrom($app['request'], $request);
        });
    }
}