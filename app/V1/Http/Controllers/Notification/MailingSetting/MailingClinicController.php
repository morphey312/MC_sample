<?php

namespace App\V1\Http\Controllers\Notification\MailingSetting;

use App\V1\Contracts\Repositories\Query\Notification\MailingSetting\NotificationMailingTemplateSettingClinicFilter;
use App\V1\Contracts\Repositories\Query\Notification\MailingSetting\NotificationMailingTemplateSettingClinicSorter;
use App\V1\Http\Requests\Notification\MailingSetting\NotificationMailingTemplateSettingClinicRequest;
use App\V1\Http\Resources\Notification\MailingSetting\MailingClinicResource;
use App\V1\Repositories\Notification\MailingSetting\NotificationMailingTemplateSettingClinicRepository;
use App\V1\Http\Controllers\BaseResourceController;
use App\V1\Models\Notification\MailingSetting\MailingClinic;

class MailingClinicController extends BaseResourceController
{

    /**
     * @var string
     */
    protected $modelClass = MailingClinic::class;

    /**
     * @var string
     */
    protected $repositoryClass = NotificationMailingTemplateSettingClinicRepository::class;

    /**
     * @var string
     */
    protected $filterClass = NotificationMailingTemplateSettingClinicFilter::class;

    /**
     * @var string
     */
    protected $sorterClass = NotificationMailingTemplateSettingClinicSorter::class;

    /**
     * @var string
     */
    protected $requestClass = NotificationMailingTemplateSettingClinicRequest::class;

    /**
     * @var string
     */
    protected $resourceClass = MailingClinicResource::class;

}
