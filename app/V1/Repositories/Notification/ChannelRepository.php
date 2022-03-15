<?php

namespace App\V1\Repositories\Notification;

use App\V1\Contracts\Repositories\Notification\ChannelRepository as RepositoryInterface;
use App\V1\Repositories\BaseRepository;
use App\V1\Models\Notification\Channel;
use App\V1\Contracts\Repositories\Query\Notification\ChannelFilter;
use App\V1\Contracts\Repositories\Query\Notification\ChannelSorter;

class ChannelRepository extends BaseRepository implements RepositoryInterface
{
    /**
     * @inherit
     */
    protected function query()
    {
        return Channel::query();
    }
    
    /**
     * @inherit
     */
    public function filter(array $filters = [])
    {
        return $this->makeFilter(ChannelFilter::class, $filters);
    }
    
    /**
     * @inherit
     */
    public function sorter(array $order = [])
    {
        return $this->makeSorter(ChannelSorter::class, $order);
    }
}