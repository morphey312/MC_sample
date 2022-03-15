<?php

namespace App\V1\Http\Controllers\Notification;

use App\V1\Http\Controllers\BaseResourceController;
use App\V1\Http\Resources\Notification\ChannelResource;
use App\V1\Contracts\Repositories\Notification\ChannelRepository;
use App\V1\Contracts\Repositories\Query\Notification\ChannelFilter;
use App\V1\Contracts\Repositories\Query\Notification\ChannelSorter;
use App\V1\Http\Requests\Notification\ChannelRequest;
use App\V1\Models\Notification\Channel;

class ChannelController extends BaseResourceController
{
    /**
     * @var string
     */
    protected $modelClass = Channel::class;

    /**
     * @var string
     */
    protected $repositoryClass = ChannelRepository::class;

    /**
     * @var string
     */
    protected $filterClass = ChannelFilter::class;

    /**
     * @var string
     */
    protected $sorterClass = ChannelSorter::class;

    /**
     * @var string
     */
    protected $requestClass = ChannelRequest::class;

    /**
     * @var string
     */
    protected $resourceClass = ChannelResource::class;
}
