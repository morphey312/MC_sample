<?php

namespace App\V1\Services\Voip\PAMI\Actions;

use PAMI\Message\Action\ActionMessage;

class ConfbridgeListRoomsAction extends ActionMessage
{
    /**
     * Constructor.
     * 
     * @return void
     */
    public function __construct()
    {
        parent::__construct('ConfbridgeListRooms');
    }
}