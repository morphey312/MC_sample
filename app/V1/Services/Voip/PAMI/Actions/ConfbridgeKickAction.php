<?php

namespace App\V1\Services\Voip\PAMI\Actions;

use PAMI\Message\Action\ActionMessage;

class ConfbridgeKickAction extends ActionMessage
{
    /**
     * Constructor.
     * 
     * @param string $channel
     * @param string $conference
     *
     * @return void
     */
    public function __construct($channel, $conference)
    {
        parent::__construct('ConfbridgeKick');
        $this->setKey('Channel', $channel);
        $this->setKey('Conference', $conference);
    }
}