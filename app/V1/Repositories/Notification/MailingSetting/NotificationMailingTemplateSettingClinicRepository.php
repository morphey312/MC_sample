<?php

namespace App\V1\Repositories\Notification\MailingSetting;

use App\V1\Contracts\Repositories\Notification\MailingSetting\MailingSettingRepository as RepositoryInterface;
use App\V1\Models\Notification\MailingSetting\MailingClinic;
use App\V1\Repositories\BaseRepository;
use App\V1\Contracts\Repositories\Query\Notification\MailingSetting\NotificationMailingTemplateSettingClinicFilter;
use App\V1\Contracts\Repositories\Query\Notification\MailingSetting\NotificationMailingTemplateSettingClinicSorter;

class NotificationMailingTemplateSettingClinicRepository extends BaseRepository implements RepositoryInterface
{
    /**
     * @inherit
     */
    protected function query()
    {
        return MailingClinic::query();
    }

    /**
     * @inherit
     */
    public function filter(array $filters = [])
    {
        return $this->makeFilter(NotificationMailingTemplateSettingClinicFilter::class, $filters);
    }

    /**
     * @inherit
     */
    public function sorter(array $order = [])
    {
        return $this->makeSorter(NotificationMailingTemplateSettingClinicSorter::class, $order);
    }
}
