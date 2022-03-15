<?php

namespace App\V1\Contracts\Services;

interface Sms
{
    /**
     * Send message to recipient
     *
     * @param string $number
     * @param string $text
     *
     * @return bool
     */
    public function sendMessage($number, $text);
}
