<?php

namespace App\V1\Contracts\Repositories;

use App\V1\Contracts\Repositories\BaseRepository;

interface VideoChatRepository extends BaseRepository
{
    /**
     * Find video chat by Room Sid
     * @param $sid
     * @param bool $exceptionOnFail
     * @return mixed
     */
    public function findBySid($sid, $exceptionOnFail = true);
}
