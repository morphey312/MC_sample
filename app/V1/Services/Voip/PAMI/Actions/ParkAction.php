<?php

namespace App\V1\Services\Voip\PAMI\Actions;

use PAMI\Message\Action\ActionMessage;

class ParkAction extends ActionMessage
{
    /**
     * Constructor.
     * 
     * @param string $channel
     * @param string $lot
     *
     * @return void
     */
    public function __construct($channel, $lot)
    {
        parent::__construct('Park');
        $this->setKey('Channel', $channel);
        $this->setKey('Timeout', 0);
        $this->setKey('Parkinglot', $lot);
    }
}