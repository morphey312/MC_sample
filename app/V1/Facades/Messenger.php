<?php

namespace App\V1\Facades;

use Illuminate\Support\Facades\Facade;

class Messenger extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'messenger';
    }
}