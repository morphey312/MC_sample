<?php

namespace App\V1\Http\Controllers\Notification;

use App\V1\Contracts\Repositories\Query\Notification\MailingProviderSorter;
use App\V1\Http\Controllers\BaseResourceController;
use App\V1\Http\Requests\Notification\MailingProviderRequest;
use App\V1\Http\Resources\Notification\ChannelResource;
use App\V1\Contracts\Repositories\Notification\MailingProviderRepository;
use App\V1\Http\Resources\Notification\MailingProviderResource;
use App\V1\Models\Notification\MailingProvider;
use App\V1\Repositories\Query\Notification\MailingProviderFilter;

class MailingProviderController extends BaseResourceController
{
    /**
     * @var string
     */
    protected $modelClass = MailingProvider::class;

    /**
     * @var string
     */
    protected $repositoryClass = MailingProviderRepository::class;

    /**
     * @var string
     */
    protected $filterClass = MailingProviderFilter::class;

    /**
     * @var string
     */
    protected $sorterClass = MailingProviderSorter::class;

    /**
     * @var string
     */
    protected $requestClass = MailingProviderRequest::class;

    /**
     * @var string
     */
    protected $resourceClass = MailingProviderResource::class;
}
