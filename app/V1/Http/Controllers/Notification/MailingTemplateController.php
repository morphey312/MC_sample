<?php

namespace App\V1\Http\Controllers\Notification;

use App\V1\Http\Controllers\BaseResourceController;
use App\V1\Http\Requests\Notification\MailingTemplateRequest;
use App\V1\Http\Resources\Notification\MailingTemplateResource;
use App\V1\Contracts\Repositories\Query\Notification\MailingTemplateFilter;
use App\V1\Contracts\Repositories\Query\Notification\MailingProviderSorter;
use App\V1\Contracts\Repositories\Query\Notification\TemplateScopes;
use App\V1\Models\Notification\MailingTemplate;
use App\V1\Repositories\Notification\MailingTemplateRepository;

class MailingTemplateController extends BaseResourceController
{
    /**
     * @var string
     */
    protected $modelClass = MailingTemplate::class;

    /**
     * @var string
     */
    protected $repositoryClass = MailingTemplateRepository::class;

    /**
     * @var string
     */
    protected $filterClass = MailingTemplateFilter::class;

    /**
     * @var string
     */
    protected $sorterClass = MailingProviderSorter::class;

    /**
     * @var string
     */
    protected $requestClass = MailingTemplateRequest::class;

    /**
     * @var string
     */
    protected $resourceClass = MailingTemplateResource::class;
}
