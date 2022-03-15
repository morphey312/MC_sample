<?php

namespace App\V1\Providers\Feature;

use Illuminate\Database\Query\Builder;

class QueryBuilderMacro
{
    /**
     * Boot feature
     * 
     * @param \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application $app
     */ 
    public static function boot($app) 
    {
        Builder::macro('whereEndsWith', function($column, $value) {
            return $this->where($column, 'like', "%{$value}");
        });
        
        Builder::macro('whereStartsWith', function($column, $value) {
            return $this->where($column, 'like', "{$value}%");
        });
        
        Builder::macro('whereContains', function($column, $value) {
            return $this->where($column, 'like', "%{$value}%");
        });

        Builder::macro('orWhereContains', function($column, $value) {
            return $this->orWhere($column, 'like', "%{$value}%");
        });
    }
}