<?php

namespace App\V1\Policies;

use App\V1\Models\User;
use App\V1\Policies\BasePolicy;
use Permissions;

class VideoChatPolicy extends BasePolicy
{
    /**
     * @var  string
     */
    protected $module = 'video-chat';

    /**
     * Check if user can request video compositions
     *
     * @param User $user
     *
     * @return bool
     */
    public function createTranscodedVideo(User $user)
    {
        return Permissions::has($user, 'video-chat.create-transcoded-video');
    }

    /**
     * Check if user can request video compositions
     *
     * @param User $user
     *
     * @return bool
     */
    public function requestRoomParticipantLogs(User $user)
    {
        return Permissions::has($user, 'video-chat.request-room-participant-logs');
    }
}
