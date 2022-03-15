<?php

namespace App\V1\Services\Voip\PAMI\Actions;

use PAMI\Message\Action\ActionMessage;

class ConfbridgeListAction extends ActionMessage
{
    /**
     * Constructor.
     * 
     * @param string $conference
     *
     * @return void
     */
    public function __construct($conference)
    {
        parent::__construct('ConfbridgeList');
        $this->setKey('Conference', $conference);
    }
}