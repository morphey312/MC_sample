<?php

namespace App\V1\Contracts\Services;

interface MailingService
{
    /**
     * Send a message
     *
     * @param Messenger\Message $message
     *
     * @return bool
     */
    public function send(Messenger\Message $message);
}
