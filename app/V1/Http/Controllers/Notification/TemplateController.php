<?php

namespace App\V1\Http\Controllers\Notification;

use App\V1\Http\Controllers\BaseResourceController;
use App\V1\Http\Resources\Notification\TemplateResource;
use App\V1\Contracts\Repositories\Notification\TemplateRepository;
use App\V1\Contracts\Repositories\Query\Notification\TemplateFilter;
use App\V1\Contracts\Repositories\Query\Notification\TemplateSorter;
use App\V1\Contracts\Repositories\Query\Notification\TemplateScopes;
use App\V1\Http\Requests\ListRequest;
use App\V1\Http\Requests\Notification\TemplateRequest;
use App\V1\Models\Notification\Template;
use App\V1\Traits\Controllers\Deleteable;

class TemplateController extends BaseResourceController
{
    /**
     * @var string
     */
    protected $modelClass = Template::class;

    /**
     * @var string
     */
    protected $repositoryClass = TemplateRepository::class;

    /**
     * @var string
     */
    protected $filterClass = TemplateFilter::class;

    /**
     * @var string
     */
    protected $sorterClass = TemplateSorter::class;

    /**
     * @var string
     */
    protected $requestClass = TemplateRequest::class;

    /**
     * @var string
     */
    protected $resourceClass = TemplateResource::class;
}
