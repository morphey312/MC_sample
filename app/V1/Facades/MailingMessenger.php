<?php

namespace App\V1\Facades;

use Illuminate\Support\Facades\Facade;

class MailingMessenger extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'mailing-messenger';
    }
}
