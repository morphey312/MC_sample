<?php

namespace App\V1\Contracts\Services;

interface VideoChat
{
    /**
     * Check room status
     * 
     * @param string $name
     * 
     * @return bool
     */
    public function checkRoomStatus($name);

    /**
     * Create new room
     * 
     * @param string $name
     * 
     * @return bool
     */
    public function createRoom($name);

    /**
     * Grant access to the room
     * 
     * @param string $room
     * @param string $identity
     * 
     * @return string
     */
    public function grantAccess($room, $identity);
}