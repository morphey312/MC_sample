<?php

namespace App\V1\Contracts\Repositories;

use App\V1\Contracts\Repositories\BaseRepository;

interface NotificationRepository extends BaseRepository
{
    public function markAsReaded();
}