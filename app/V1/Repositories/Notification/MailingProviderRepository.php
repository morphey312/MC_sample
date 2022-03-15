<?php

namespace App\V1\Repositories\Notification;

use App\V1\Contracts\Repositories\Notification\MailingProviderRepository as RepositoryInterface;
use \App\V1\Contracts\Repositories\Query\Notification\MailingProviderFilter;
use App\V1\Contracts\Repositories\Query\Notification\MailingProviderSorter;
use App\V1\Models\Notification\MailingProvider;
use App\V1\Repositories\BaseRepository;

class MailingProviderRepository extends BaseRepository implements RepositoryInterface
{
    /**
     * @inherit
     */
    protected function query()
    {
        return MailingProvider::query();
    }

    /**
     * @inherit
     */
    public function filter(array $filters = [])
    {
        return $this->makeFilter(MailingProviderFilter::class, $filters);
    }

    /**
     * @inherit
     */
    public function sorter(array $order = [])
    {
        return $this->makeSorter(MailingProviderSorter::class, $order);
    }
}
